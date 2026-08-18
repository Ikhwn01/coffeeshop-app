<?php
// admin/orders.php - Order & POS Transaction Management
require_once __DIR__ . '/../config/database.php';

// Auth Guard before any output
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Action Updates (Status Change / Payment Status Change) BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    
    if ($_POST['action'] === 'update_status') {
        $new_status = sanitize($_POST['order_status']);
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
    } elseif ($_POST['action'] === 'update_payment') {
        $new_payment = sanitize($_POST['payment_status']);
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->execute([$new_payment, $order_id]);
    }

    header("Location: orders.php");
    exit;
}

// Filter Parameters
$status_filter  = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$payment_filter = isset($_GET['payment']) ? sanitize($_GET['payment']) : '';
$date_filter    = isset($_GET['date']) ? sanitize($_GET['date']) : '';
$search_query   = isset($_GET['q']) ? sanitize($_GET['q']) : '';

// Build Query dynamically
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if ($status_filter) {
    $sql .= " AND order_status = ?";
    $params[] = $status_filter;
}
if ($payment_filter) {
    $sql .= " AND payment_status = ?";
    $params[] = $payment_filter;
}
if ($date_filter) {
    $sql .= " AND DATE(created_at) = ?";
    $params[] = $date_filter;
}
if ($search_query) {
    $sql .= " AND (order_code LIKE ? OR customer_name LIKE ? OR table_number LIKE ?)";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
    $params[] = "%{$search_query}%";
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$page_title = __('admin_orders', 'Manajemen Pesanan & Transaksi');
$admin_active = "orders";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Filter Bar -->
<div style="background: #ffffff; border: 1px solid var(--admin-border); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);">
    <form action="orders.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
        <div style="flex-grow: 1; min-width: 220px;">
            <input type="text" name="q" class="form-control" placeholder="<?php echo get_current_lang() == 'en' ? 'Search Code / Customer / Table...' : 'Cari Kode / Nama / Meja...'; ?>" value="<?php echo htmlspecialchars($search_query); ?>">
        </div>

        <div>
            <select name="status" class="form-control" style="width: auto;">
                <option value="">-- <?php echo get_current_lang() == 'en' ? 'All Order Statuses' : 'Semua Status Pesanan'; ?> --</option>
                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>🟡 Pending</option>
                <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>🔵 Processing</option>
                <option value="ready" <?php echo $status_filter == 'ready' ? 'selected' : ''; ?>>🟢 Ready</option>
                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>✅ Completed</option>
                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
            </select>
        </div>

        <div>
            <select name="payment" class="form-control" style="width: auto;">
                <option value="">-- <?php echo get_current_lang() == 'en' ? 'Payment Status' : 'Status Pembayaran'; ?> --</option>
                <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>💳 <?php echo get_current_lang() == 'en' ? 'Paid' : 'Lunas (Paid)'; ?></option>
                <option value="unpaid" <?php echo $payment_filter == 'unpaid' ? 'selected' : ''; ?>>⏳ <?php echo get_current_lang() == 'en' ? 'Unpaid' : 'Belum Bayar (Unpaid)'; ?></option>
            </select>
        </div>

        <div>
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>" style="width: auto;">
        </div>

        <button type="submit" class="btn" style="background: var(--admin-accent); color: #ffffff; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i class="fas fa-filter"></i> <?php echo __('admin_filter', 'Filter'); ?>
        </button>
        
        <?php if ($status_filter || $payment_filter || $date_filter || $search_query): ?>
            <a href="orders.php" style="color: var(--admin-muted); font-size: 0.85rem; text-decoration: none; font-weight: 600; padding: 0.5rem;">
                <i class="fas fa-undo"></i> Reset
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Orders Table -->
<div class="card-table">
    <div class="card-header">
        <h3><i class="fas fa-list" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Transactions List' : 'Daftar Transaksi'; ?> (<?php echo count($orders); ?>)</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?php echo __('status_order_time', 'Waktu'); ?></th>
                    <th><?php echo __('status_order_code', 'Kode Pesanan'); ?></th>
                    <th><?php echo __('status_customer_name', 'Pelanggan'); ?></th>
                    <th><?php echo __('nav_table', 'Meja'); ?></th>
                    <th><?php echo __('payment_method', 'Metode'); ?></th>
                    <th><?php echo __('status_total', 'Total'); ?></th>
                    <th><?php echo __('status_payment_status', 'Status Bayar'); ?></th>
                    <th><?php echo __('status_prep_status', 'Status Pesanan'); ?></th>
                    <th style="text-align: center;"><?php echo __('admin_action', 'Aksi'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--admin-muted); padding: 3rem;">
                            <i class="fas fa-inbox" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem;"></i>
                            <?php echo get_current_lang() == 'en' ? 'No transactions found.' : 'Tidak ada transaksi ditemukan.'; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td style="font-size: 0.82rem; color: var(--admin-muted); white-space: nowrap;">
                                <i class="far fa-clock" style="color: var(--admin-accent);"></i> <?php echo date('d/m/Y H:i', strtotime($ord['created_at'])); ?>
                            </td>
                            <td>
                                <strong style="color: var(--admin-accent-dark); font-family: 'JetBrains Mono', monospace; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($ord['order_code']); ?>
                                </strong>
                            </td>
                            <td>
                                <strong style="color: var(--admin-text-main);"><?php echo htmlspecialchars($ord['customer_name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--admin-accent-light); color: var(--admin-accent-dark); border-color: rgba(200, 138, 72, 0.3);">
                                    <i class="fas fa-chair"></i> <?php echo htmlspecialchars($ord['table_number']); ?>
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--admin-text);">
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($ord['payment_method']); ?></span>
                            </td>
                            <td>
                                <strong style="color: var(--admin-text-main); font-size: 0.95rem;"><?php echo format_rupiah($ord['total_amount']); ?></strong>
                            </td>
                            
                            <!-- Payment Status Toggle Form -->
                            <td>
                                <form action="orders.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_payment">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                    <select name="payment_status" onchange="this.form.submit()" class="badge <?php echo $ord['payment_status'] == 'paid' ? 'badge-completed' : 'badge-pending'; ?>" style="cursor: pointer; outline: none; font-family: inherit;">
                                        <option value="unpaid" <?php echo $ord['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>⏳ UNPAID</option>
                                        <option value="paid" <?php echo $ord['payment_status'] == 'paid' ? 'selected' : ''; ?>>💳 PAID (<?php echo get_current_lang() == 'en' ? 'Paid' : 'Lunas'; ?>)</option>
                                    </select>
                                </form>
                            </td>

                            <!-- Order Status Change Form -->
                            <td>
                                <form action="orders.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                    <select name="order_status" onchange="this.form.submit()" class="badge badge-<?php echo $ord['order_status']; ?>" style="cursor: pointer; outline: none; font-family: inherit;">
                                        <option value="pending" <?php echo $ord['order_status'] == 'pending' ? 'selected' : ''; ?>>🟡 PENDING</option>
                                        <option value="processing" <?php echo $ord['order_status'] == 'processing' ? 'selected' : ''; ?>>🔵 PROCESSING</option>
                                        <option value="ready" <?php echo $ord['order_status'] == 'ready' ? 'selected' : ''; ?>>🟢 READY</option>
                                        <option value="completed" <?php echo $ord['order_status'] == 'completed' ? 'selected' : ''; ?>>✅ COMPLETED</option>
                                        <option value="cancelled" <?php echo $ord['order_status'] == 'cancelled' ? 'selected' : ''; ?>>❌ CANCELLED</option>
                                    </select>
                                </form>
                            </td>

                            <td style="text-align: center;">
                                <a href="order_detail.php?id=<?php echo $ord['id']; ?>" class="btn-action-detail" title="<?php echo get_current_lang() == 'en' ? 'View Invoice & Print Receipt' : 'Lihat Detail & Cetak Struk'; ?>">
                                    <i class="fas fa-receipt" style="color: var(--admin-accent);"></i>
                                    <span><?php echo get_current_lang() == 'en' ? 'Receipt' : 'Struk'; ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
