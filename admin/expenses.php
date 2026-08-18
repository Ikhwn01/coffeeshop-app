<?php
// admin/expenses.php - Operational Expenses Management (Input Pengeluaran)
require_once __DIR__ . '/../config/database.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$msg = null;

// Handle Add/Delete Expense BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_expense') {
        $title        = sanitize($_POST['title'] ?? '');
        $category     = sanitize($_POST['category'] ?? 'Bahan Baku');
        $amount       = floatval($_POST['amount'] ?? 0);
        $expense_date = sanitize($_POST['expense_date'] ?? date('Y-m-d'));
        $description  = sanitize($_POST['description'] ?? '');
        $created_by   = $_SESSION['fullname'] ?? 'Admin';

        if ($title && $amount > 0) {
            $stmt = $pdo->prepare("INSERT INTO expenses (title, category, amount, expense_date, description, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $category, $amount, $expense_date, $description, $created_by]);
            $msg = get_current_lang() == 'en' ? "Expense '{$title}' of " . format_rupiah($amount) . " recorded successfully!" : "Pengeluaran '{$title}' sebesar " . format_rupiah($amount) . " berhasil dicatat!";
        }
    } elseif ($_POST['action'] === 'delete_expense') {
        $expense_id = intval($_POST['expense_id']);
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$expense_id]);
        $msg = get_current_lang() == 'en' ? "Expense record deleted." : "Catatan pengeluaran dihapus.";
    }
}

// Total Expenses Summary
$total_expense_all = floatval($pdo->query("SELECT SUM(amount) as total FROM expenses")->fetch()['total'] ?? 0);
$total_expense_month = floatval($pdo->query("SELECT SUM(amount) as total FROM expenses WHERE MONTH(expense_date) = MONTH(CURRENT_DATE()) AND YEAR(expense_date) = YEAR(CURRENT_DATE())")->fetch()['total'] ?? 0);

// Fetch all expenses ordered by date
$expenses = $pdo->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC")->fetchAll();

$page_title = __('admin_expenses', 'Input & Manajemen Pengeluaran');
$admin_active = "expenses";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h3 style="color: var(--admin-text-main); margin: 0; font-size: 1.2rem;"><?php echo get_current_lang() == 'en' ? 'Operational Cash Expenses' : 'Pengeluaran Operasional Cafe'; ?></h3>
        <p style="color: var(--admin-muted); font-size: 0.85rem; margin: 0.25rem 0 0;"><?php echo get_current_lang() == 'en' ? 'Track all outgoing expenses for raw materials, operations, utilities, and maintenance.' : 'Catat seluruh kas keluar untuk bahan baku, operasional, dan perawatan.'; ?></p>
    </div>

    <button data-modal-target="addExpenseModal" class="btn" style="background: #dc2626; color: #ffffff; font-weight: 700; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
        <i class="fas fa-plus"></i> <?php echo __('admin_add_expense', 'Input Pengeluaran Baru'); ?>
    </button>
</div>

<?php if ($msg): ?>
    <div style="background: var(--success-bg); border: 1px solid #a7f3d0; color: #047857; padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<!-- Summary Widget Cards -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--danger-bg); color: #dc2626;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? "This Month's Total Expenses" : "Total Pengeluaran Bulan Ini"; ?></h4>
            <div class="stat-value" style="color: #dc2626;"><?php echo format_rupiah($total_expense_month); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: var(--admin-accent-light); color: var(--admin-accent-dark);">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? "Accumulated Total Expenses" : "Total Akumulasi Pengeluaran"; ?></h4>
            <div class="stat-value"><?php echo format_rupiah($total_expense_all); ?></div>
        </div>
    </div>
</div>

<!-- Expense List Table -->
<div class="card-table">
    <div class="card-header">
        <h3><i class="fas fa-file-invoice-dollar" style="color: #dc2626;"></i> <?php echo get_current_lang() == 'en' ? 'Expense History' : 'Riwayat Pengeluaran'; ?> (<?php echo count($expenses); ?>)</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?php echo get_current_lang() == 'en' ? 'Date' : 'Tanggal'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Title / Transaction' : 'Judul / Transaksi'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Category' : 'Kategori'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Amount' : 'Jumlah (Rp)'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Notes' : 'Keterangan'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Recorded By' : 'Dicatat Oleh'; ?></th>
                    <th style="text-align: center;"><?php echo __('admin_action', 'Aksi'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 3rem;"><?php echo get_current_lang() == 'en' ? 'No expenses recorded yet.' : 'Belum ada pengeluaran dicatat.'; ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                        <tr>
                            <td style="font-size: 0.85rem; color: var(--admin-muted);"><?php echo date('d/m/Y', strtotime($e['expense_date'])); ?></td>
                            <td><strong style="color: var(--admin-text-main);"><?php echo htmlspecialchars($e['title']); ?></strong></td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: var(--admin-text);">
                                    <?php echo htmlspecialchars($e['category']); ?>
                                </span>
                            </td>
                            <td><strong style="color: #dc2626;"><?php echo format_rupiah($e['amount']); ?></strong></td>
                            <td style="font-size: 0.85rem; color: var(--admin-muted); max-width: 250px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                <?php echo !empty($e['description']) ? htmlspecialchars($e['description']) : '-'; ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--admin-muted);"><?php echo htmlspecialchars($e['created_by']); ?></td>
                            <td style="text-align: center;">
                                <form action="expenses.php" method="POST" onsubmit="return confirm('<?php echo get_current_lang() == 'en' ? 'Delete this expense record?' : 'Hapus catatan pengeluaran ini?'; ?>');" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_expense">
                                    <input type="hidden" name="expense_id" value="<?php echo $e['id']; ?>">
                                    <button type="submit" style="background: #fff; border: 1px solid #fecaca; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;" title="<?php echo get_current_lang() == 'en' ? 'Delete' : 'Hapus'; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Input Pengeluaran -->
<div class="admin-modal" id="addExpenseModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="color: var(--admin-text-main); font-size: 1.1rem;"><i class="fas fa-file-invoice-dollar" style="color: #dc2626;"></i> <?php echo __('admin_add_expense', 'Catat Pengeluaran Baru'); ?></h3>
            <button data-modal-close class="close-btn" style="color: var(--admin-muted); background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="expenses.php" method="POST">
            <input type="hidden" name="action" value="add_expense">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Expense Title / Transaction *' : 'Judul Transaksi Pengeluaran *'; ?></label>
                    <input type="text" name="title" class="form-control" placeholder="<?php echo get_current_lang() == 'en' ? 'e.g. 5kg Arabica Coffee Beans Restock' : 'Contoh: Pembelian Biji Kopi Arabica 5kg'; ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Expense Category *' : 'Kategori Pengeluaran *'; ?></label>
                        <select name="category" class="form-control" required>
                            <option value="Bahan Baku"><?php echo get_current_lang() == 'en' ? 'Raw Materials (Coffee/Milk/Syrup)' : 'Bahan Baku (Kopi/Susu/Syrup)'; ?></option>
                            <option value="Operasional"><?php echo get_current_lang() == 'en' ? 'Operations (Power/Water/Internet)' : 'Operasional (Listrik/Air/Internet)'; ?></option>
                            <option value="Kemasan"><?php echo get_current_lang() == 'en' ? 'Packaging & Cups' : 'Kemasan & Cup (Packaging)'; ?></option>
                            <option value="Gaji Staf"><?php echo get_current_lang() == 'en' ? 'Staff Wages & Benefits' : 'Gaji & Utilitas Staf'; ?></option>
                            <option value="Perawatan Alat"><?php echo get_current_lang() == 'en' ? 'Machine Maintenance & Repair' : 'Perawatan & Reparasi Mesin'; ?></option>
                            <option value="Lain-lain"><?php echo get_current_lang() == 'en' ? 'Other' : 'Lain-lain'; ?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Amount (Rp) *' : 'Nominal (Rp) *'; ?></label>
                        <input type="number" name="amount" class="form-control" placeholder="500000" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Transaction Date *' : 'Tanggal Transaksi *'; ?></label>
                    <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Notes / Details' : 'Keterangan / Catatan Tambahan'; ?></label>
                    <textarea name="description" class="form-control" rows="3" placeholder="<?php echo get_current_lang() == 'en' ? 'Weekly supplier restock invoice...' : 'Restock mingguan supplier A...'; ?>"></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%; background: #dc2626; color: #ffffff; font-weight: 700; padding: 0.85rem; border: none; border-radius: 8px; cursor: pointer; margin-top: 1rem; font-size: 0.95rem;">
                    <i class="fas fa-save"></i> <?php echo get_current_lang() == 'en' ? 'Save Expense' : 'Simpan Catatan Pengeluaran'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
