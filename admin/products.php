<?php
// admin/products.php - Menu & Category CRUD Management
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

    if ($action === 'add_product') {
        $name        = sanitize($_POST['name'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 1);
        $price       = floatval($_POST['price'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        $is_available= isset($_POST['is_available']) ? 1 : 0;

        if ($name && $price > 0) {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, is_available) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $name, $description, $price, $is_available]);
            $msg = get_current_lang() == 'en' ? "New menu item '{$name}' added successfully!" : "Menu baru '{$name}' berhasil ditambahkan!";
        }
    } elseif ($action === 'toggle_availability') {
        $product_id = intval($_POST['product_id']);
        $current = intval($_POST['current_status']);
        $new_status = $current === 1 ? 0 : 1;

        $stmt = $pdo->prepare("UPDATE products SET is_available = ? WHERE id = ?");
        $stmt->execute([$new_status, $product_id]);
        $msg = get_current_lang() == 'en' ? "Product availability status updated." : "Status ketersediaan produk telah diperbarui.";
    } elseif ($action === 'delete_product') {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $msg = get_current_lang() == 'en' ? "Product deleted successfully." : "Menu berhasil dihapus.";
    }
}

// Fetch categories & products
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$products   = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.category_id ASC, p.name ASC")->fetchAll();

$page_title = __('admin_products', 'Manajemen Menu & Kategori');
$admin_active = "products";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Action Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h3 style="color: var(--admin-text-main); margin: 0; font-size: 1.2rem;"><?php echo get_current_lang() == 'en' ? 'Coffee Shop Menu Catalog' : 'Daftar Menu Coffee Shop'; ?> (<?php echo count($products); ?>)</h3>
    
    <button data-modal-target="addProductModal" class="btn" style="background: var(--admin-accent); color: #ffffff; font-weight: 700; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
        <i class="fas fa-plus"></i> <?php echo __('admin_add_product', 'Tambah Menu Baru'); ?>
    </button>
</div>

<?php if ($msg): ?>
    <div style="background: var(--success-bg); border: 1px solid #a7f3d0; color: #047857; padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<!-- Products Data Table -->
<div class="card-table">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?php echo get_current_lang() == 'en' ? 'Product Name' : 'Nama Menu'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Category' : 'Kategori'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Price' : 'Harga'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Description' : 'Deskripsi'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Availability' : 'Status Stok'; ?></th>
                    <th style="text-align: center;"><?php echo __('admin_action', 'Aksi'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td style="color: var(--admin-muted); font-family: 'JetBrains Mono', monospace;">#<?php echo $p['id']; ?></td>
                        <td><strong style="color: var(--admin-text-main); font-size: 0.95rem;"><?php echo htmlspecialchars($p['name']); ?></strong></td>
                        <td>
                            <span class="badge" style="background: var(--admin-accent-light); color: var(--admin-accent-dark); border-color: rgba(200, 138, 72, 0.3);">
                                <?php echo htmlspecialchars($p['category_name']); ?>
                            </span>
                        </td>
                        <td><strong style="color: var(--admin-text-main);"><?php echo format_rupiah($p['price']); ?></strong></td>
                        <td style="font-size: 0.85rem; color: var(--admin-muted); max-width: 250px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                            <?php echo htmlspecialchars($p['description']); ?>
                        </td>
                        <td>
                            <form action="products.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="toggle_availability">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $p['is_available']; ?>">
                                <button type="submit" style="background: transparent; border: none; cursor: pointer;" class="badge <?php echo $p['is_available'] ? 'badge-ready' : 'badge-cancelled'; ?>">
                                    <?php echo $p['is_available'] ? (get_current_lang() == 'en' ? '🟢 In Stock' : '🟢 Tersedia') : (get_current_lang() == 'en' ? '🔴 Out of Stock' : '🔴 Habis'); ?>
                                </button>
                            </form>
                        </td>
                        <td style="text-align: center;">
                            <form action="products.php" method="POST" onsubmit="return confirm('<?php echo get_current_lang() == 'en' ? 'Are you sure you want to delete this menu item?' : 'Yakin ingin menghapus menu ini?'; ?>');" style="display: inline;">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" style="background: #fff; border: 1px solid #fecaca; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-trash-alt"></i> <?php echo get_current_lang() == 'en' ? 'Delete' : 'Hapus'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Produk -->
<div class="admin-modal" id="addProductModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="color: var(--admin-text-main); font-size: 1.1rem;"><i class="fas fa-utensils" style="color: var(--admin-accent);"></i> <?php echo __('admin_add_product', 'Tambah Menu Produk Baru'); ?></h3>
            <button data-modal-close class="close-btn" style="color: var(--admin-muted); background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="products.php" method="POST">
            <input type="hidden" name="action" value="add_product">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Menu Item Name *' : 'Nama Menu *'; ?></label>
                    <input type="text" name="name" class="form-control" placeholder="<?php echo get_current_lang() == 'en' ? 'e.g. Iced Aren Latte' : 'Contoh: Aren Latte Iced'; ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Category *' : 'Kategori *'; ?></label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Price (Rp) *' : 'Harga (Rp) *'; ?></label>
                        <input type="number" name="price" class="form-control" placeholder="25000" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php echo get_current_lang() == 'en' ? 'Product Description' : 'Deskripsi Produk'; ?></label>
                    <textarea name="description" class="form-control" rows="3" placeholder="<?php echo get_current_lang() == 'en' ? 'Flavor profile, ingredients, or tasting notes...' : 'Deskripsi rasa atau komposisi menu...'; ?>"></textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--admin-text-main);">
                        <input type="checkbox" name="is_available" value="1" checked style="width: 18px; height: 18px; accent-color: var(--admin-accent);">
                        <span style="font-size: 0.9rem; font-weight: 500;"><?php echo get_current_lang() == 'en' ? 'Immediate Availability (In Stock)' : 'Status Langsung Tersedia (In Stock)'; ?></span>
                    </label>
                </div>

                <button type="submit" class="btn" style="width: 100%; background: var(--admin-accent); color: #ffffff; font-weight: 700; padding: 0.85rem; border: none; border-radius: 8px; cursor: pointer; margin-top: 1rem; font-size: 0.95rem;">
                    <i class="fas fa-save"></i> <?php echo get_current_lang() == 'en' ? 'Save Menu Item' : 'Simpan Menu'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
