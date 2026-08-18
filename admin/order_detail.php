<?php
// admin/order_detail.php - Order Detail & Thermal Receipt Printable View
require_once __DIR__ . '/../config/database.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

$page_title = get_current_lang() == 'en' ? "Order Invoice & Thermal Receipt" : "Detail & Struk Pesanan Kasir";
$admin_active = "orders";

require_once __DIR__ . '/../includes/admin_header.php';

if (!$order) {
    echo "<div style='background: var(--danger-bg); color: #dc2626; padding: 1.5rem; border-radius: 10px; margin: 1.5rem;'>" . (get_current_lang() == 'en' ? "Order not found! <a href='orders.php'>Back to orders list</a>" : "Pesanan tidak ditemukan! <a href='orders.php'>Kembali ke daftar pesanan</a>") . "</div>";
    include __DIR__ . '/../includes/admin_footer.php';
    exit;
}

// Fetch Order Items
$stmtItems = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmtItems->execute([$order_id]);
$order_items = $stmtItems->fetchAll();

// Prepare WhatsApp Text
$waText = "Halo " . $order['customer_name'] . ", terima kasih telah memesan di Warm Brew Cafe!%0A%0A"
    . "*STRUK PEMESANAN KAFE*%0A"
    . "No. Nota: " . $order['order_code'] . "%0A"
    . "Meja: " . $order['table_number'] . "%0A"
    . "Waktu: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "%0A%0A"
    . "*Rincian Pesanan:*%0A";
foreach ($order_items as $it) {
    $waText .= "- " . $it['product_name'] . " (" . $it['quantity'] . "x) : Rp " . number_format($it['subtotal'], 0, ',', '.') . "%0A";
}
$waText .= "%0A*TOTAL: " . format_rupiah($order['total_amount']) . "*%0A"
    . "Status: " . strtoupper($order['payment_status']) . " (" . $order['payment_method'] . ")%0A%0A"
    . "Cek status pesanan Anda secara live:%0A"
    . "http://" . $_SERVER['HTTP_HOST'] . "/order_status.php?code=" . $order['order_code'];
?>

<script>document.body.classList.add('has-thermal-receipt');</script>

<div style="display: flex; gap: 2.5rem; flex-wrap: wrap; align-items: flex-start;">
    
    <!-- Printable Thermal POS Receipt Box -->
    <div style="background: #ffffff; color: #1e293b; font-family: 'JetBrains Mono', 'Courier New', Courier, monospace; padding: 2rem 1.75rem; width: 350px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 10px 30px rgba(0,0,0,0.06); position: relative;" id="printable-receipt">
        
        <!-- Header / Logo -->
        <div style="text-align: center; margin-bottom: 1.25rem; border-bottom: 2px dashed #94a3b8; padding-bottom: 1rem;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 6px; margin-bottom: 0.25rem;">
                <i class="fas fa-coffee" style="font-size: 1.4rem; color: #c88a48;"></i>
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: #0f172a; letter-spacing: 0.5px;">WARM BREW CAFE</h2>
            </div>
            <p style="font-size: 0.78rem; margin: 0.2rem 0; color: #475569;">Jl. Senopati No. 42, Kebayoran Baru, Jakarta</p>
            <p style="font-size: 0.78rem; margin: 0; color: #475569;">Telp / WA: +62 812-3456-7890</p>
        </div>

        <!-- Receipt Metadata -->
        <div style="font-size: 0.82rem; margin-bottom: 1rem; border-bottom: 1px dashed #cbd5e1; padding-bottom: 0.85rem; line-height: 1.6; color: #334155;">
            <div style="display: flex; justify-content: space-between;">
                <span><?php echo get_current_lang() == 'en' ? 'Invoice No:' : 'No. Nota:'; ?></span>
                <strong style="color: #0f172a;"><?php echo htmlspecialchars($order['order_code']); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span><?php echo get_current_lang() == 'en' ? 'Date/Time:' : 'Waktu:'; ?></span>
                <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span><?php echo get_current_lang() == 'en' ? 'Cashier:' : 'Kasir:'; ?></span>
                <span><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Kasir Utama'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span><?php echo get_current_lang() == 'en' ? 'Customer:' : 'Pelanggan:'; ?></span>
                <strong style="color: #0f172a;"><?php echo htmlspecialchars($order['customer_name']); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span><?php echo get_current_lang() == 'en' ? 'Table:' : 'Meja / Tipe:'; ?></span>
                <span><strong>Meja <?php echo htmlspecialchars($order['table_number']); ?></strong> (<?php echo strtoupper($order['order_type']); ?>)</span>
            </div>
        </div>

        <!-- Items Table -->
        <table style="width: 100%; font-size: 0.82rem; border-collapse: collapse; margin-bottom: 1rem;">
            <thead>
                <tr style="border-bottom: 1px dashed #94a3b8; text-align: left; color: #475569;">
                    <th style="padding-bottom: 0.4rem; font-weight: 700;">Menu</th>
                    <th style="text-align: center; padding-bottom: 0.4rem; font-weight: 700;">Qty</th>
                    <th style="text-align: right; padding-bottom: 0.4rem; font-weight: 700;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td style="padding: 0.35rem 0; font-weight: 600; color: #0f172a;">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: normal;">@ Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                        </td>
                        <td style="text-align: center; vertical-align: top; padding-top: 0.35rem; color: #334155;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right; vertical-align: top; padding-top: 0.35rem; font-weight: 600; color: #0f172a;"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals & Payment Breakdown -->
        <div style="border-top: 2px dashed #94a3b8; padding-top: 0.75rem; font-size: 0.85rem; line-height: 1.6;">
            <div style="display: flex; justify-content: space-between; color: #475569;">
                <span>Subtotal</span>
                <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; color: #475569;">
                <span>Pajak & Servis</span>
                <span>Termasuk (0%)</span>
            </div>
            
            <div style="border-top: 1px dashed #cbd5e1; margin-top: 0.5rem; padding-top: 0.5rem; font-size: 1.05rem; font-weight: 800; display: flex; justify-content: space-between; color: #0f172a;">
                <span>TOTAL AKHIR</span>
                <span style="color: #c88a48;"><?php echo format_rupiah($order['total_amount']); ?></span>
            </div>
        </div>

        <!-- Payment Method Badge -->
        <div style="margin-top: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #64748b;">Metode: <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong></span>
            <span style="font-weight: 700; color: <?php echo $order['payment_status'] === 'paid' ? '#059669' : '#dc2626'; ?>;">
                ● <?php echo strtoupper($order['payment_status']); ?>
            </span>
        </div>

        <!-- Barcode / Footer -->
        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.75rem; border-top: 2px dashed #94a3b8; padding-top: 1rem; color: #475569;">
            <p style="margin: 0; font-weight: 700; color: #0f172a;"><?php echo get_current_lang() == 'en' ? 'Thank You For Your Visit!' : 'Terima Kasih Atas Kunjungan Anda!'; ?></p>
            <p style="margin: 0.25rem 0 0; font-size: 0.72rem;">Wi-Fi: <strong>WarmBrew_Guest</strong> (Pass: <code>coffee123</code>)</p>
            <p style="margin: 0.25rem 0 0; font-size: 0.72rem; color: #94a3b8;">Instagram: @warmbrewcafe</p>
        </div>
    </div>

    <!-- Action & Detail Panel -->
    <div style="flex-grow: 1; max-width: 480px;">
        <div style="background: #ffffff; border: 1px solid var(--admin-border); border-radius: 12px; padding: 1.75rem; box-shadow: var(--shadow-sm);">
            <h3 style="color: var(--admin-text-main); margin-bottom: 1.25rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 0.75rem; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-print" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Cashier Receipt Actions' : 'Tindakan Struk & Kasir'; ?>
            </h3>

            <!-- Primary Buttons -->
            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                <button onclick="window.print()" class="btn" style="background: var(--admin-accent); color: #ffffff; font-weight: 700; padding: 0.9rem 1.25rem; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(200, 138, 72, 0.25);">
                    <i class="fas fa-print"></i> <?php echo get_current_lang() == 'en' ? 'Print Thermal Receipt (80mm / 58mm)' : 'Cetak Struk Kasir Thermal (80mm)'; ?>
                </button>

                <a href="https://api.whatsapp.com/send?text=<?php echo $waText; ?>" target="_blank" class="btn" style="background: #059669; color: #ffffff; font-weight: 700; padding: 0.85rem 1.25rem; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; font-size: 0.9rem;">
                    <i class="fab fa-whatsapp"></i> <?php echo get_current_lang() == 'en' ? 'Share Receipt via WhatsApp' : 'Kirim Struk via WhatsApp'; ?>
                </a>

                <a href="orders.php" class="btn" style="background: #f8fafc; color: var(--admin-text); border: 1px solid #cbd5e1; padding: 0.75rem 1.25rem; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600; font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> <?php echo get_current_lang() == 'en' ? 'Back to Orders List' : 'Kembali ke Daftar Transaksi'; ?>
                </a>
            </div>

            <!-- Order Meta Breakdown -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; font-size: 0.88rem; color: #334155; line-height: 1.8;">
                <div><strong><?php echo get_current_lang() == 'en' ? 'Special Customer Notes:' : 'Catatan Khusus Pelanggan:'; ?></strong></div>
                <div style="color: #64748b; margin-bottom: 0.75rem; font-style: italic;">
                    <?php echo !empty($order['notes']) ? htmlspecialchars($order['notes']) : (get_current_lang() == 'en' ? 'No special instructions.' : 'Tidak ada catatan khusus.'); ?>
                </div>

                <div style="display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem;">
                    <span><?php echo __('order_type', 'Tipe Pesanan'); ?>:</span>
                    <strong><?php echo strtoupper($order['order_type']); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span><?php echo get_current_lang() == 'en' ? 'Kitchen Preparation Status:' : 'Status Dapur / Barista:'; ?></span>
                    <span class="badge badge-<?php echo $order['order_status']; ?>"><?php echo strtoupper($order['order_status']); ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
