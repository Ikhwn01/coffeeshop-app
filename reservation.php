<?php
// reservation.php - Online Table Reservation Page
require_once __DIR__ . '/config/database.php';

$page_title = __('meta_title_reservation', 'Reservasi Meja Online');
$active_nav = "reservation";

$success_res = null;
$error_msg   = null;

// Fetch all available tables
$stmtTables = $pdo->query("SELECT * FROM tables ORDER BY table_number ASC");
$tables = $stmtTables->fetchAll();

// Pre-select table if table_id is passed in URL query
$selected_table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_id        = intval($_POST['table_id'] ?? 0);
    $customer_name   = sanitize($_POST['customer_name'] ?? '');
    $customer_phone  = sanitize($_POST['customer_phone'] ?? '');
    $customer_email  = sanitize($_POST['customer_email'] ?? '');
    $reservation_date = sanitize($_POST['reservation_date'] ?? '');
    $reservation_time = sanitize($_POST['reservation_time'] ?? '');
    $number_of_guests = intval($_POST['number_of_guests'] ?? 1);
    $notes            = sanitize($_POST['notes'] ?? '');

    if ($table_id <= 0 || empty($customer_name) || empty($customer_phone) || empty($reservation_date) || empty($reservation_time)) {
        $error_msg = get_current_lang() == 'en' ? "Please complete all required fields (*)." : "Harap lengkapi semua kolom wajib (*).";
    } else {
        // Generate unique reservation code
        $res_code = 'RES-' . rand(100000, 999999);

        try {
            $stmt = $pdo->prepare("INSERT INTO reservations (reservation_code, table_id, customer_name, customer_phone, customer_email, reservation_date, reservation_time, number_of_guests, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([
                $res_code, $table_id, $customer_name, $customer_phone, $customer_email, $reservation_date, $reservation_time, $number_of_guests, $notes
            ]);

            $success_res = [
                'code' => $res_code,
                'name' => $customer_name,
                'date' => $reservation_date,
                'time' => $reservation_time,
                'guests' => $number_of_guests
            ];
        } catch (\PDOException $e) {
            $error_msg = (get_current_lang() == 'en' ? "Failed to submit reservation: " : "Gagal memproses reservasi: ") . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding: 3rem 0; max-width: 850px;">
    
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="hero-subtitle"><i class="far fa-calendar-check"></i> <?php echo __('res_subtitle', 'Online Booking'); ?></span>
        <h1 class="hero-title" style="font-size: 2.5rem;"><?php echo __('res_title', 'Reservasi Meja Cafe'); ?></h1>
        <p style="color: var(--text-secondary);"><?php echo __('res_desc', 'Amankan tempat terbaik Anda untuk kumpul keluarga, rapat bisnis, atau sekadar bersantai.'); ?></p>
    </div>

    <?php if ($success_res): ?>
        <div style="background: rgba(46, 196, 182, 0.1); border: 1.5px solid var(--success); padding: 2rem; border-radius: var(--radius-lg); text-align: center; margin-bottom: 2rem;">
            <i class="fas fa-check-circle" style="font-size: 3.5rem; color: var(--success); margin-bottom: 1rem;"></i>
            <h2 style="color: var(--text-primary); margin-bottom: 0.5rem;"><?php echo __('res_success_title', 'Reservasi Berhasil Dikirim!'); ?></h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;"><?php echo __('res_success_desc', 'Simpan kode reservasi di bawah ini untuk konfirmasi saat tiba di lokasi.'); ?></p>
            
            <div style="background: var(--bg-card); display: inline-block; padding: 1rem 2rem; border-radius: var(--radius-md); border: 1px dashed var(--primary); font-size: 1.5rem; font-weight: 800; color: var(--primary); letter-spacing: 2px;">
                <?php echo htmlspecialchars($success_res['code']); ?>
            </div>

            <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($success_res['name']); ?></span>
                <span><i class="far fa-calendar"></i> <?php echo htmlspecialchars($success_res['date']); ?> (<?php echo htmlspecialchars($success_res['time']); ?>)</span>
                <span><i class="fas fa-users"></i> <?php echo $success_res['guests']; ?> <?php echo get_current_lang() == 'en' ? 'Guests' : 'Tamu'; ?></span>
            </div>

            <div style="margin-top: 2rem;">
                <a href="reservation.php" class="btn btn-outline"><?php echo __('res_btn_new', 'Buat Reservasi Baru'); ?></a>
                <a href="index.php" class="btn btn-primary"><?php echo __('res_btn_menu', 'Lihat Menu Coffee Shop'); ?></a>
            </div>
        </div>
    <?php else: ?>

        <?php if ($error_msg): ?>
            <div style="background: rgba(231, 29, 54, 0.15); border: 1px solid var(--danger); padding: 1rem; border-radius: var(--radius-sm); color: #ff8fa3; margin-bottom: 1.5rem;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 2.5rem; box-shadow: var(--shadow-soft);">
            <form action="reservation.php" method="POST">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-chair" style="color: var(--primary);"></i> <?php echo __('res_select_table', 'Pilih Meja *'); ?></label>
                    <select name="table_id" class="form-control" required>
                        <option value=""><?php echo __('res_select_table_opt', '-- Pilih Meja --'); ?></option>
                        <?php foreach ($tables as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($selected_table_id == $t['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['table_number']); ?> - <?php echo htmlspecialchars($t['location']); ?> (<?php echo get_current_lang() == 'en' ? 'Capacity: ' . $t['capacity'] . ' Guests' : 'Kapasitas: ' . $t['capacity'] . ' Orang'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user" style="color: var(--primary);"></i> <?php echo __('res_name', 'Nama Lengkap Pemesan *'); ?></label>
                        <input type="text" name="customer_name" class="form-control" placeholder="<?php echo __('res_name_placeholder', 'Nama lengkap Anda...'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-phone" style="color: var(--primary);"></i> <?php echo __('res_phone', 'Nomor WhatsApp / HP *'); ?></label>
                        <input type="tel" name="customer_phone" class="form-control" placeholder="<?php echo __('res_phone_placeholder', '0812xxxxxxx'); ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><i class="far fa-calendar" style="color: var(--primary);"></i> <?php echo __('res_date', 'Tanggal *'); ?></label>
                        <input type="date" name="reservation_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="far fa-clock" style="color: var(--primary);"></i> <?php echo __('res_time', 'Jam Kedatangan *'); ?></label>
                        <input type="time" name="reservation_time" class="form-control" value="18:00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-users" style="color: var(--primary);"></i> <?php echo __('res_guests', 'Jumlah Tamu *'); ?></label>
                        <input type="number" name="number_of_guests" class="form-control" min="1" max="20" value="2" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="far fa-envelope" style="color: var(--primary);"></i> <?php echo __('res_email', 'Email (Opsional)'); ?></label>
                    <input type="email" name="customer_email" class="form-control" placeholder="<?php echo __('res_email_placeholder', 'email@domain.com'); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="far fa-sticky-note" style="color: var(--primary);"></i> <?php echo __('res_notes', 'Catatan / Request Khusus'); ?></label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="<?php echo __('res_notes_placeholder', 'Misal: Meja dekat jendela, ada anak kecil...'); ?>"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1rem; margin-top: 1rem;">
                    <i class="fas fa-calendar-check"></i> <?php echo __('res_btn_submit', 'Konfirmasi Booking Sekarang'); ?>
                </button>
            </form>
        </div>

    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
