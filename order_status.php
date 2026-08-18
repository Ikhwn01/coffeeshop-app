<?php
// order_status.php - Customer Order Tracking Page
require_once __DIR__ . '/config/database.php';

$page_title = __('meta_title_status', 'Cek Status Pesanan');
$active_nav = "status";

$code = isset($_GET['code']) ? sanitize($_GET['code']) : '';
$order = null;
$order_items = [];

if ($code) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ?");
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if ($order) {
        $stmtItems = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmtItems->execute([$order['id']]);
        $order_items = $stmtItems->fetchAll();
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding: 3rem 0; max-width: 800px;">
    
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="hero-subtitle"><i class="fas fa-search-location"></i> <?php echo __('status_page_subtitle', 'Live Status Tracking'); ?></span>
        <h1 class="hero-title" style="font-size: 2.5rem;"><?php echo __('status_page_title', 'Status Pesanan Anda'); ?></h1>
        <p style="color: var(--text-secondary);"><?php echo __('status_page_desc', 'Masukkan kode unik pesanan yang Anda dapatkan saat memesan.'); ?></p>
    </div>

    <!-- Search Code Box -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 2.5rem;">
        <form action="order_status.php" method="GET" style="display: flex; gap: 1rem;">
            <input type="text" name="code" class="form-control" placeholder="<?php echo __('status_input_placeholder', 'Masukkan Kode Pesanan (contoh: ORD-20260801-01)...'); ?>" value="<?php echo htmlspecialchars($code); ?>" required style="flex-grow: 1;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> <?php echo __('status_btn_search', 'Cek Status'); ?>
            </button>
        </form>
    </div>

    <?php if ($code && !$order): ?>
        <div style="text-align: center; padding: 3rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
            <i class="fas fa-search-minus" style="font-size: 3.5rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--text-primary);"><?php echo __('status_not_found_title', 'Pesanan Tidak Ditemukan'); ?></h3>
            <p style="color: var(--text-secondary);"><?php echo __('status_order_code', 'Kode Pesanan'); ?> <code><?php echo htmlspecialchars($code); ?></code> <?php echo __('status_not_found_desc', 'tidak terdaftar di sistem kami.'); ?></p>
        </div>
    <?php elseif ($order): ?>

        <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-soft);">
            
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 1.5rem; gap: 1rem;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo __('status_order_code', 'Kode Pesanan'); ?></div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo htmlspecialchars($order['order_code']); ?></div>
                </div>

                <div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo __('status_payment_status', 'Status Pembayaran'); ?></div>
                    <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'badge-completed' : 'badge-pending'; ?>">
                        <?php echo strtoupper($order['payment_status']); ?> (<?php echo htmlspecialchars($order['payment_method']); ?>)
                    </span>
                </div>
            </div>

            <!-- Order Details Overview -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; background: rgba(0, 0, 0, 0.2); padding: 1rem; border-radius: var(--radius-md);">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo __('status_customer_name', 'Nama Pemesan'); ?></div>
                    <div style="font-weight: 700;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo __('status_location_table', 'Lokasi / Meja'); ?></div>
                    <div style="font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($order['table_number']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo __('status_order_type', 'Tipe Pesanan'); ?></div>
                    <div style="font-weight: 700; text-transform: uppercase;"><?php echo str_replace('_', ' ', $order['order_type']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo __('status_order_time', 'Waktu Pesan'); ?></div>
                    <div style="font-weight: 600; font-size: 0.85rem;"><?php echo date('H:i, d M Y', strtotime($order['created_at'])); ?></div>
                </div>
            </div>

            <!-- Status Timeline Badge -->
            <div style="text-align: center; margin: 2rem 0; padding: 1.5rem; background: rgba(212, 154, 89, 0.08); border-radius: var(--radius-md); border: 1px solid var(--glass-border);">
                <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.5rem;"><?php echo __('status_prep_status', 'Status Pembuatan'); ?></div>
                
                <?php
                $status_labels = [
                    'pending'    => [__('status_pending_label', 'Menunggu Konfirmasi Barista'), 'fa-clock', 'warning'],
                    'processing' => [__('status_processing_label', 'Sedang Disiapkan / Diseduh'), 'fa-fire', 'processing'],
                    'ready'      => [__('status_ready_label', 'Pesanan Siap Disajikan!'), 'fa-mug-hot', 'ready'],
                    'completed'  => [__('status_completed_label', 'Pesanan Selesai'), 'fa-check-circle', 'completed'],
                    'cancelled'  => [__('status_cancelled_label', 'Pesanan Dibatalkan'), 'fa-times-circle', 'cancelled']
                ];
                $st_info = $status_labels[$order['order_status']] ?? ['Unknown', 'fa-info-circle', 'pending'];
                ?>

                <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                    <i class="fas <?php echo $st_info[1]; ?>" style="color: var(--primary);"></i>
                    <span><?php echo $st_info[0]; ?></span>
                </div>
            </div>

            <!-- Itemized Summary -->
            <h4 style="margin-bottom: 1rem; color: var(--text-primary); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;"><?php echo __('status_order_items', 'Rincian Pesanan'); ?></h4>
            <div style="margin-bottom: 1.5rem;">
                <?php foreach ($order_items as $item): ?>
                    <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px dashed var(--border-color); font-size: 0.95rem;">
                        <div>
                            <strong><?php echo $item['quantity']; ?>x</strong> <?php echo htmlspecialchars($item['product_name']); ?>
                            <?php if ($item['notes']): ?>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">(<?php echo htmlspecialchars($item['notes']); ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-weight: 700; color: var(--primary);">
                            <?php echo format_rupiah($item['subtotal']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--text-primary); padding-top: 1rem; border-top: 2px solid var(--border-color);">
                <span><?php echo __('total_payment', 'Total Pembayaran'); ?></span>
                <span style="color: var(--primary);"><?php echo format_rupiah($order['total_amount']); ?></span>
            </div>

            <div style="margin-top: 2rem; text-align: center;">
                <button onclick="window.location.reload();" class="btn btn-outline">
                    <i class="fas fa-sync-alt"></i> <?php echo get_current_lang() == 'en' ? 'Refresh Status' : 'Segarkan Status'; ?>
                </button>
            </div>
        </div>

    <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
