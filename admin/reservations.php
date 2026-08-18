<?php
// admin/reservations.php - Customer Table Reservation Management
require_once __DIR__ . '/../config/database.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Reservation Status Updates BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_res_status') {
        $res_id = intval($_POST['res_id']);
        $new_status = sanitize($_POST['status']);

        $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $res_id]);
    }
    header("Location: reservations.php");
    exit;
}

$reservations = $pdo->query("SELECT r.*, t.table_number, t.location FROM reservations r JOIN tables t ON r.table_id = t.id ORDER BY r.id DESC")->fetchAll();

$page_title = __('admin_reservations', 'Manajemen Reservasi Meja');
$admin_active = "reservations";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="card-table">
    <div class="card-header">
        <h3><i class="fas fa-calendar-alt" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Customer Table Bookings' : 'Data Booking Reservasi Pelanggan'; ?> (<?php echo count($reservations); ?>)</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?php echo __('res_code', 'Kode Booking'); ?></th>
                    <th><?php echo __('status_customer_name', 'Nama Pelanggan'); ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Contact / WhatsApp' : 'Kontak / WA'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Table & Location' : 'Meja & Lokasi'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Date & Time' : 'Tgl & Jam'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Guests' : 'Jumlah'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Notes' : 'Catatan'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Status' : 'Status'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--admin-muted); padding: 3rem;">
                            <i class="far fa-calendar-times" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 0.75rem;"></i>
                            <?php echo get_current_lang() == 'en' ? 'No table reservations found.' : 'Belum ada booking reservasi.'; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><strong style="color: var(--admin-accent-dark); font-family: 'JetBrains Mono', monospace;"><?php echo htmlspecialchars($r['reservation_code']); ?></strong></td>
                            <td><strong style="color: var(--admin-text-main);"><?php echo htmlspecialchars($r['customer_name']); ?></strong></td>
                            <td>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $r['customer_phone']); ?>" target="_blank" style="color: #047857; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; background: var(--success-bg); padding: 0.25rem 0.65rem; border-radius: 6px; border: 1px solid #a7f3d0;">
                                    <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($r['customer_phone']); ?>
                                </a>
                            </td>
                            <td>
                                <span style="color: var(--admin-text-main); font-weight: 700;"><?php echo htmlspecialchars($r['table_number']); ?></span>
                                <span style="font-size: 0.78rem; color: var(--admin-muted); display: block;"><?php echo htmlspecialchars($r['location']); ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo date('d/m/Y', strtotime($r['reservation_date'])); ?></div>
                                <div style="font-size: 0.8rem; color: var(--admin-muted);"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($r['reservation_time'])); ?></div>
                            </td>
                            <td><strong><?php echo $r['number_of_guests']; ?> <?php echo get_current_lang() == 'en' ? 'Guests' : 'Orang'; ?></strong></td>
                            <td style="font-size: 0.85rem; color: var(--admin-muted); max-width: 200px;">
                                <?php echo !empty($r['notes']) ? htmlspecialchars($r['notes']) : '-'; ?>
                            </td>
                            <td>
                                <form action="reservations.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_res_status">
                                    <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="badge badge-<?php echo $r['status'] == 'confirmed' ? 'ready' : ($r['status'] == 'pending' ? 'pending' : ($r['status'] == 'completed' ? 'completed' : 'cancelled')); ?>" style="cursor: pointer; outline: none; font-family: inherit;">
                                        <option value="pending" <?php echo $r['status'] == 'pending' ? 'selected' : ''; ?>>🟡 PENDING</option>
                                        <option value="confirmed" <?php echo $r['status'] == 'confirmed' ? 'selected' : ''; ?>>🟢 CONFIRMED (<?php echo get_current_lang() == 'en' ? 'Approved' : 'Disetujui'; ?>)</option>
                                        <option value="completed" <?php echo $r['status'] == 'completed' ? 'selected' : ''; ?>>✅ COMPLETED</option>
                                        <option value="cancelled" <?php echo $r['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ CANCELLED</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
