<?php
// admin/index.php - Staff & Financial Dashboard
require_once __DIR__ . '/../config/database.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Calculate Today's Stats
$today = date('Y-m-d');

// 1. Total Income Today (Paid orders)
$stmtIncome = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid' AND DATE(created_at) = ?");
$stmtIncome->execute([$today]);
$income_today = floatval($stmtIncome->fetch()['total'] ?? 0);

// 2. Total Orders Count Today
$stmtCount = $pdo->prepare("SELECT COUNT(*) as cnt FROM orders WHERE DATE(created_at) = ?");
$stmtCount->execute([$today]);
$orders_today_cnt = intval($stmtCount->fetch()['cnt'] ?? 0);

// 3. Total Expenses Today
$stmtExp = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE expense_date = ?");
$stmtExp->execute([$today]);
$expenses_today = floatval($stmtExp->fetch()['total'] ?? 0);

// 4. Net Profit Today
$net_profit_today = $income_today - $expenses_today;

// Fetch Recent 6 Active / Pending Orders
$stmtRecent = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6");
$recent_orders = $stmtRecent->fetchAll();

// Fetch Pending Reservations Count
$stmtResCnt = $pdo->query("SELECT COUNT(*) as cnt FROM reservations WHERE status = 'pending'");
$pending_reservations_cnt = intval($stmtResCnt->fetch()['cnt'] ?? 0);

$page_title = __('meta_title_admin_dashboard', 'Dashboard Staf & Keuangan');
$admin_active = "dashboard";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Stat Cards Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
            <i class="fas fa-coins"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo __('admin_today_revenue', 'Pendapatan Hari Ini'); ?></h4>
            <div class="stat-value" style="color: #059669;"><?php echo format_rupiah($income_today); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fef2f2; color: #dc2626;">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? "Today's Expenses" : "Pengeluaran Hari Ini"; ?></h4>
            <div class="stat-value" style="color: #dc2626;"><?php echo format_rupiah($expenses_today); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: var(--admin-accent-light); color: var(--admin-accent-dark);">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? "Today's Net Profit" : "Laba Bersih Hari Ini"; ?></h4>
            <div class="stat-value" style="color: <?php echo $net_profit_today >= 0 ? '#059669' : '#dc2626'; ?>;">
                <?php echo format_rupiah($net_profit_today); ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo __('admin_today_orders', 'Pesanan Hari Ini'); ?></h4>
            <div class="stat-value"><?php echo $orders_today_cnt; ?> <?php echo get_current_lang() == 'en' ? 'Orders' : 'Transaksi'; ?></div>
        </div>
    </div>
</div>

<!-- Alert for Pending Reservations -->
<?php if ($pending_reservations_cnt > 0): ?>
    <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 0.75rem; color: #b45309;">
            <i class="fas fa-bell" style="font-size: 1.25rem;"></i>
            <span><?php echo get_current_lang() == 'en' ? 'There are <strong>' . $pending_reservations_cnt . ' new table reservations</strong> awaiting confirmation!' : 'Ada <strong>' . $pending_reservations_cnt . ' reservasi meja baru</strong> menunggu konfirmasi!'; ?></span>
        </div>
        <a href="reservations.php" style="background: #f59e0b; color: #ffffff; padding: 0.4rem 1.1rem; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;">
            <?php echo get_current_lang() == 'en' ? 'Review Bookings' : 'Tinjau Reservasi'; ?> <i class="fas fa-arrow-right"></i>
        </a>
    </div>
<?php endif; ?>

<!-- Recent Orders Table -->
<div class="card-table">
    <div class="card-header">
        <h3><i class="fas fa-clock" style="color: var(--admin-accent);"></i> <?php echo __('admin_recent_orders', 'Pesanan Terbaru'); ?></h3>
        <a href="orders.php" style="color: var(--admin-accent-dark); font-size: 0.85rem; text-decoration: none; font-weight: 700;">
            <?php echo __('admin_view_all', 'Lihat Semua'); ?> <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?php echo __('status_order_code', 'Kode Pesanan'); ?></th>
                    <th><?php echo __('status_customer_name', 'Pelanggan'); ?></th>
                    <th><?php echo __('nav_table', 'Meja'); ?></th>
                    <th><?php echo __('status_total', 'Total'); ?></th>
                    <th><?php echo __('status_payment_status', 'Status Pembayaran'); ?></th>
                    <th><?php echo __('status_prep_status', 'Status Pesanan'); ?></th>
                    <th><?php echo __('status_order_time', 'Waktu'); ?></th>
                    <th style="text-align: center;"><?php echo __('admin_action', 'Aksi'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--admin-muted); padding: 3rem;">
                            <i class="fas fa-inbox" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem;"></i>
                            <?php echo get_current_lang() == 'en' ? 'No orders recorded yet.' : 'Belum ada pesanan masuk.'; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $ord): ?>
                        <tr>
                            <td><strong style="color: var(--admin-accent-dark); font-family: 'JetBrains Mono', monospace; font-size: 0.95rem;"><?php echo htmlspecialchars($ord['order_code']); ?></strong></td>
                            <td><strong style="color: var(--admin-text-main);"><?php echo htmlspecialchars($ord['customer_name']); ?></strong></td>
                            <td>
                                <span class="badge" style="background: var(--admin-accent-light); color: var(--admin-accent-dark); border-color: rgba(200, 138, 72, 0.3);">
                                    <i class="fas fa-chair"></i> <?php echo htmlspecialchars($ord['table_number']); ?>
                                </span>
                            </td>
                            <td><strong style="color: var(--admin-text-main);"><?php echo format_rupiah($ord['total_amount']); ?></strong></td>
                            <td>
                                <span class="badge <?php echo $ord['payment_status'] == 'paid' ? 'badge-completed' : 'badge-pending'; ?>">
                                    <?php echo strtoupper($ord['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $ord['order_status']; ?>">
                                    <?php echo strtoupper($ord['order_status']); ?>
                                </span>
                            </td>
                            <td style="color: var(--admin-muted); font-size: 0.85rem;"><?php echo date('H:i', strtotime($ord['created_at'])); ?></td>
                            <td style="text-align: center;">
                                <a href="order_detail.php?id=<?php echo $ord['id']; ?>" class="btn-action-detail">
                                    <i class="fas fa-receipt" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Receipt' : 'Struk'; ?>
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
