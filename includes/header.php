<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Warm Brew Coffee Shop" : "Warm Brew - Artisan Coffee Shop & Lounge"; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-wrapper">
            <a href="index.php" class="logo">
                <i class="fas fa-mug-hot"></i>
                <span>WARM BREW</span>
            </a>

            <div class="nav-links">
                <a href="index.php" class="nav-link <?php echo (!isset($active_nav) || $active_nav == 'menu') ? 'active' : ''; ?>"><?php echo __('nav_menu', 'Menu & Pesan'); ?></a>
                <a href="reservation.php" class="nav-link <?php echo (isset($active_nav) && $active_nav == 'reservation') ? 'active' : ''; ?>"><?php echo __('nav_reservation', 'Reservasi Meja'); ?></a>
                <a href="order_status.php" class="nav-link <?php echo (isset($active_nav) && $active_nav == 'status') ? 'active' : ''; ?>"><?php echo __('nav_order_status', 'Cek Status Pesanan'); ?></a>
            </div>

            <div class="nav-actions">
                <!-- Language Switcher -->
                <div class="lang-switcher" style="display: inline-flex; align-items: center; background: rgba(255,255,255,0.06); border-radius: 20px; padding: 2px 6px; border: 1px solid rgba(255,255,255,0.12); font-size: 0.8rem; font-weight: 700; gap: 4px;">
                    <a href="?lang=id" style="color: <?php echo get_current_lang() == 'id' ? '#d49a59' : '#8c95a0'; ?>; text-decoration: none; padding: 2px 6px; border-radius: 12px; <?php echo get_current_lang() == 'id' ? 'background: rgba(212,154,89,0.2); font-weight: 800;' : ''; ?>" title="Bahasa Indonesia">🇮🇩 ID</a>
                    <span style="color: rgba(255,255,255,0.2); font-size: 0.7rem;">|</span>
                    <a href="?lang=en" style="color: <?php echo get_current_lang() == 'en' ? '#d49a59' : '#8c95a0'; ?>; text-decoration: none; padding: 2px 6px; border-radius: 12px; <?php echo get_current_lang() == 'en' ? 'background: rgba(212,154,89,0.2); font-weight: 800;' : ''; ?>" title="English">🇬🇧 EN</a>
                </div>

                <div id="currentTableBadge" class="table-indicator" style="display: none;">
                    <i class="fas fa-chair"></i> <span><?php echo __('nav_table', 'Meja'); ?>: -</span>
                </div>

                <button class="btn-icon" id="openCartBtn" title="<?php echo __('nav_view_cart', 'Lihat Keranjang'); ?>">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </button>

                <a href="admin/login.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <i class="fas fa-user-shield"></i> <?php echo __('nav_staff_login', 'Staf Login'); ?>
                </a>
            </div>
        </div>
    </nav>
