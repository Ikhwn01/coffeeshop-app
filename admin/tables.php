<?php
// admin/tables.php - Manage Tables & QR/Link Generators
require_once __DIR__ . '/../config/database.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$msg = null;

// Handle CRUD Actions BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_table') {
        $table_number = sanitize($_POST['table_number'] ?? '');
        $capacity     = intval($_POST['capacity'] ?? 2);
        $location     = sanitize($_POST['location'] ?? 'Indoor');

        if ($table_number) {
            $stmt = $pdo->prepare("INSERT INTO tables (table_number, capacity, location, status) VALUES (?, ?, ?, 'available')");
            $stmt->execute([$table_number, $capacity, $location]);
            $msg = get_current_lang() == 'en' ? "Table {$table_number} added successfully!" : "Meja {$table_number} berhasil ditambahkan!";
        }
    } elseif ($action === 'update_table_status') {
        $table_id   = intval($_POST['table_id']);
        $new_status = sanitize($_POST['status']);

        $stmt = $pdo->prepare("UPDATE tables SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $table_id]);
        $msg = get_current_lang() == 'en' ? "Table status updated." : "Status meja telah diperbarui.";
    }
}

$tables = $pdo->query("SELECT * FROM tables ORDER BY table_number ASC")->fetchAll();

$page_title = __('admin_tables', 'Kelola Meja & Link QR');
$admin_active = "tables";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h3 style="color: var(--admin-text-main); margin: 0; font-size: 1.2rem;"><?php echo get_current_lang() == 'en' ? 'Coffee Shop Tables' : 'Daftar Meja Coffee Shop'; ?> (<?php echo count($tables); ?>)</h3>
    <button data-modal-target="addTableModal" class="btn" style="background: var(--admin-accent); color: #ffffff; font-weight: 700; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
        <i class="fas fa-plus"></i> <?php echo __('admin_add_table', 'Tambah Meja Baru'); ?>
    </button>
</div>

<?php if ($msg): ?>
    <div style="background: var(--success-bg); border: 1px solid #a7f3d0; color: #047857; padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<!-- Tables Grid Display -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <?php foreach ($tables as $t): 
        $st_class = $t['status'] == 'available' ? 'badge-ready' : ($t['status'] == 'occupied' ? 'badge-cancelled' : 'badge-pending');
    ?>
        <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="color: var(--admin-accent-dark); font-size: 1.35rem; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-chair" style="color: var(--admin-accent);"></i> <?php echo htmlspecialchars($t['table_number']); ?>
                    </h3>
                    <span class="badge <?php echo $st_class; ?>"><?php echo strtoupper($t['status']); ?></span>
                </div>

                <div style="font-size: 0.88rem; color: var(--admin-muted); margin-bottom: 1.25rem; line-height: 1.6;">
                    <div><i class="fas fa-map-marker-alt" style="color: var(--admin-accent); width: 18px;"></i> <?php echo get_current_lang() == 'en' ? 'Location' : 'Lokasi'; ?>: <strong style="color: var(--admin-text-main);"><?php echo htmlspecialchars($t['location']); ?></strong></div>
                    <div><i class="fas fa-users" style="color: var(--admin-accent); width: 18px;"></i> <?php echo get_current_lang() == 'en' ? 'Capacity' : 'Kapasitas'; ?>: <strong style="color: var(--admin-text-main);"><?php echo $t['capacity']; ?> <?php echo get_current_lang() == 'en' ? 'Persons' : 'Orang'; ?></strong></div>
                </div>

                <!-- Direct Links for QR Code Scanning -->
                <div style="background: #f8fafc; padding: 0.85rem; border-radius: 8px; border: 1px dashed #cbd5e1; font-size: 0.8rem; margin-bottom: 1.25rem;">
                    <div style="color: var(--admin-text-main); font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-qrcode" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Direct Table Order Link (QR):' : 'Link Pemesanan Meja Ini:'; ?>
                    </div>
                    <code style="color: var(--admin-accent-dark); word-break: break-all; font-size: 0.85rem;">index.php?table=<?php echo urlencode($t['table_number']); ?></code>
                </div>
            </div>

            <!-- Status Form Switch -->
            <form action="tables.php" method="POST" style="margin: 0; display: flex; gap: 0.5rem;">
                <input type="hidden" name="action" value="update_table_status">
                <input type="hidden" name="table_id" value="<?php echo $t['id']; ?>">
                
                <select name="status" onchange="this.form.submit()" class="form-control" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; cursor: pointer; font-weight: 600;">
                    <option value="available" <?php echo $t['status'] == 'available' ? 'selected' : ''; ?>>🟢 <?php echo get_current_lang() == 'en' ? 'Available' : 'Tersedia (Available)'; ?></option>
                    <option value="occupied" <?php echo $t['status'] == 'occupied' ? 'selected' : ''; ?>>🔴 <?php echo get_current_lang() == 'en' ? 'Occupied' : 'Terisi (Occupied)'; ?></option>
                    <option value="reserved" <?php echo $t['status'] == 'reserved' ? 'selected' : ''; ?>>🟡 <?php echo get_current_lang() == 'en' ? 'Reserved' : 'Direservasi (Reserved)'; ?></option>
                </select>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Tambah Meja -->
<div class="admin-modal" id="addTableModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="color: var(--admin-text-main); font-size: 1.1rem;"><i class="fas fa-chair" style="color: var(--admin-accent);"></i> <?php echo __('admin_add_table', 'Tambah Meja Baru'); ?></h3>
            <button data-modal-close class="close-btn" style="color: var(--admin-muted); background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="tables.php" method="POST">
            <input type="hidden" name="action" value="add_table">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Table Number / Label *' : 'Nomor / Label Meja *'; ?></label>
                    <input type="text" name="table_number" class="form-control" placeholder="<?php echo get_current_lang() == 'en' ? 'e.g. M-07, VIP-2' : 'Contoh: M-07, VIP-2'; ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Seat Capacity *' : 'Kapasitas Tamu *'; ?></label>
                        <input type="number" name="capacity" class="form-control" min="1" max="50" value="4" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Location Area' : 'Area / Lokasi'; ?></label>
                        <input type="text" name="location" class="form-control" placeholder="<?php echo get_current_lang() == 'en' ? 'Indoor Main, Outdoor Terrace, VIP' : 'Indoor Utama, Outdoor, VIP'; ?>">
                    </div>
                </div>

                <button type="submit" class="btn" style="width: 100%; background: var(--admin-accent); color: #ffffff; font-weight: 700; padding: 0.85rem; border: none; border-radius: 8px; cursor: pointer; margin-top: 1rem;">
                    <i class="fas fa-save"></i> <?php echo get_current_lang() == 'en' ? 'Save Table' : 'Simpan Data Meja'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
