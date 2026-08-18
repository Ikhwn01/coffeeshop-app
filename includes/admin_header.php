<?php
// includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database config is loaded
require_once __DIR__ . '/../config/database.php';

// Auth Guard: redirect to login if user session is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_fullname = $_SESSION['fullname'] ?? 'Staf';
$user_role     = $_SESSION['role'] ?? 'karyawan';
?>
<!DOCTYPE html>
<html lang="<?php echo get_current_lang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Admin BrewPOS" : "Dashboard Staf & Keuangan BrewPOS"; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Sidebar (Non-Scrollable, Perfectly Fitted) -->
    <aside class="sidebar">
        <div class="sidebar-header" style="display: flex; align-items: center; gap: 10px;">
            <img src="../assets/img/logo.png" alt="BrewPOS Logo" style="height: 32px; width: 32px; border-radius: 6px; object-fit: cover; box-shadow: 0 2px 6px rgba(0,0,0,0.4);">
            <span style="font-weight: 800; letter-spacing: 0.5px;">BrewPOS</span>
        </div>

        <div class="sidebar-menu">
            <div>
                <div class="menu-category"><?php echo get_current_lang() == 'en' ? 'Main Menu' : 'Menu Utama'; ?></div>
                <a href="index.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-pie"></i> <span><?php echo __('admin_dashboard', 'Dashboard'); ?></span>
                </a>
                <a href="orders.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'orders') ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register"></i> <span><?php echo __('admin_orders', 'Pesanan & Transaksi'); ?></span>
                </a>
                <a href="reservations.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'reservations') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> <span><?php echo __('admin_reservations', 'Reservasi Meja'); ?></span>
                </a>

                <div class="menu-category"><?php echo get_current_lang() == 'en' ? 'Cafe Management' : 'Manajemen Cafe'; ?></div>
                <a href="products.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'products') ? 'active' : ''; ?>">
                    <i class="fas fa-utensils"></i> <span><?php echo __('admin_products', 'Menu & Kategori'); ?></span>
                </a>
                <a href="tables.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'tables') ? 'active' : ''; ?>">
                    <i class="fas fa-chair"></i> <span><?php echo __('admin_tables', 'Kelola Meja'); ?></span>
                </a>

                <div class="menu-category"><?php echo get_current_lang() == 'en' ? 'Finance & Reports' : 'Keuangan & Laporan'; ?></div>
                <a href="expenses.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'expenses') ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i> <span><?php echo __('admin_expenses', 'Pengeluaran Kas'); ?></span>
                </a>
                <a href="reports.php" class="menu-item <?php echo (isset($admin_active) && $admin_active == 'reports') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> <span><?php echo __('admin_reports', 'Laporan Keuangan'); ?></span>
                </a>
            </div>

            <div style="border-top: 1px solid var(--admin-sidebar-border); padding-top: 0.4rem; margin-top: 0.4rem;">
                <a href="../index.php" target="_blank" class="menu-item">
                    <i class="fas fa-external-link-alt"></i> <span><?php echo __('admin_customer_web', 'Web Customer'); ?></span>
                </a>
                <a href="logout.php" class="menu-item" style="color: #f87171;">
                    <i class="fas fa-sign-out-alt"></i> <span><?php echo __('admin_logout', 'Keluar'); ?></span>
                </a>
            </div>
        </div>

        <div class="user-profile">
            <div class="user-info">
                <h5><?php echo htmlspecialchars($user_fullname); ?></h5>
                <p>Role: <?php echo ucfirst($user_role); ?></p>
            </div>
            <a href="logout.php" style="color: #9c8a7e;" title="Logout"><i class="fas fa-power-off"></i></a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <header class="topbar">
            <div class="page-title">
                <?php echo isset($page_title) ? htmlspecialchars($page_title) : "Dashboard Staf"; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.85rem; color: var(--admin-muted);">
                <!-- Language Switcher -->
                <div class="lang-switcher" style="display: inline-flex; align-items: center; background: #fbf3ea; border-radius: 20px; padding: 2px 6px; border: 1px solid #ede4dc; font-size: 0.75rem; font-weight: 700; gap: 4px;">
                    <a href="?lang=id" style="color: <?php echo get_current_lang() == 'id' ? '#ffffff' : '#8c7a6e'; ?>; text-decoration: none; padding: 2px 7px; border-radius: 12px; <?php echo get_current_lang() == 'id' ? 'background: #d49a59; font-weight: 800;' : ''; ?>" title="Bahasa Indonesia">🇮🇩 ID</a>
                    <span style="color: #ede4dc; font-size: 0.7rem;">|</span>
                    <a href="?lang=en" style="color: <?php echo get_current_lang() == 'en' ? '#ffffff' : '#8c7a6e'; ?>; text-decoration: none; padding: 2px 7px; border-radius: 12px; <?php echo get_current_lang() == 'en' ? 'background: #d49a59; font-weight: 800;' : ''; ?>" title="English">🇬🇧 EN</a>
                </div>
                <span><i class="far fa-calendar" style="color: var(--admin-accent);"></i> <?php echo date('d M Y'); ?></span>
                <span class="badge" style="background: var(--admin-accent-light); color: var(--admin-accent-dark); border: 1px solid rgba(212, 154, 89, 0.3);">
                    <?php echo strtoupper($user_role); ?> MODE
                </span>
            </div>
        </header>
        <main class="main-content">
