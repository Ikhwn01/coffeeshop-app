<?php
// index.php - Main Customer Page
require_once __DIR__ . '/config/database.php';

$page_title = __('meta_title_home', 'Menu & Pesan Online');
$active_nav = "menu";

// Fetch categories from DB
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmtCat->fetchAll();

// Fetch products from DB
$stmtProd = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_available = 1 ORDER BY p.category_id ASC, p.name ASC");
$products = $stmtProd->fetchAll();

// Check if table URL parameter is set
$table_number = isset($_GET['table']) ? sanitize($_GET['table']) : '';

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<section class="hero">
    <div class="container">
        <span class="hero-subtitle"><i class="fas fa-seedling"></i> <?php echo __('hero_subtitle', 'Speciality Craft Coffee'); ?></span>
        <h1 class="hero-title"><?php echo __('hero_title', 'Nikmati Cita Rasa Kopi Sejati & Makanan Lezat'); ?></h1>
        <p class="hero-description"><?php echo __('hero_desc', 'Pesan langsung dari meja Anda tanpa perlu mengantre. Pilih menu favorit, isi nama & nomor meja Anda, lalu biarkan barista kami melayani Anda.'); ?></p>
        
        <?php if ($table_number): ?>
            <div style="margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(212, 154, 89, 0.2); border: 1px solid var(--primary); padding: 0.5rem 1.25rem; border-radius: 50px; color: var(--primary); font-weight: 700;">
                <i class="fas fa-map-marker-alt"></i> <?php echo __('hero_table_indicator', 'Anda sedang berada di'); ?> <strong><?php echo __('nav_table', 'Meja'); ?> <?php echo htmlspecialchars($table_number); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Filter & Product Catalog Section -->
<section class="container filter-section">
    <div class="filter-bar">
        <div class="category-tabs">
            <button class="category-btn active" data-category="all">
                <i class="fas fa-th-large"></i> <?php echo __('all_menu', 'Semua Menu'); ?>
            </button>
            <?php foreach ($categories as $cat): ?>
                <button class="category-btn" data-category="<?php echo htmlspecialchars($cat['slug']); ?>">
                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i> <?php echo htmlspecialchars($cat['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="productSearchInput" placeholder="<?php echo __('search_placeholder', 'Cari menu favorit...'); ?>">
        </div>
    </div>
</section>

<!-- Products Display Grid -->
<main class="container">
    <div class="products-grid">
        <?php foreach ($products as $prod): 
            // Fallback image gradient background generator based on ID
            $gradients = [
                'linear-gradient(135deg, #2b1007 0%, #472111 100%)',
                'linear-gradient(135deg, #1b262c 0%, #0f4c81 100%)',
                'linear-gradient(135deg, #261c14 0%, #543828 100%)',
                'linear-gradient(135deg, #162421 0%, #204037 100%)'
            ];
            $bgStyle = $gradients[$prod['id'] % count($gradients)];
        ?>
            <div class="product-card" 
                 data-id="<?php echo $prod['id']; ?>" 
                 data-name="<?php echo htmlspecialchars($prod['name']); ?>" 
                 data-price="<?php echo $prod['price']; ?>" 
                 data-category="<?php echo htmlspecialchars($prod['category_slug']); ?>">
                
                <div class="product-img-wrapper" style="background: <?php echo $bgStyle; ?>; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <i class="fas <?php echo htmlspecialchars($prod['category_slug'] == 'espresso-based' || $prod['category_slug'] == 'manual-brew' ? 'fa-mug-hot' : ($prod['category_slug'] == 'food-main' ? 'fa-utensils' : 'fa-glass-cheers')); ?>" style="font-size: 3.5rem; opacity: 0.7;"></i>
                    <span class="badge-category"><?php echo htmlspecialchars($prod['category_name']); ?></span>
                </div>

                <div class="product-content">
                    <h3 class="product-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
                    <p class="product-desc"><?php echo htmlspecialchars($prod['description']); ?></p>
                    
                    <div class="product-footer">
                        <span class="product-price"><?php echo format_rupiah($prod['price']); ?></span>
                        <button class="btn btn-primary btn-add-cart">
                            <i class="fas fa-plus"></i> <?php echo __('btn_add', 'Tambah'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
