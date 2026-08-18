<?php
// config/database.php - Hybrid Database Connection (Local & Cloud Deployment)

// 1. Read Environment Variables for Cloud Deployment (Vercel / TiDB / Aiven / Railway)
$host    = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
$port    = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306');
$db      = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'coffeeshop_db');
$user    = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$pass    = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '');
$charset = 'utf8mb4';

// 2. Parse DATABASE_URL / MYSQL_URL connection string if provided
$db_url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
if ($db_url) {
    $parsed = parse_url($db_url);
    if ($parsed) {
        $host = $parsed['host'] ?? $host;
        $port = $parsed['port'] ?? $port;
        $user = $parsed['user'] ?? $user;
        $pass = $parsed['pass'] ?? $pass;
        $db   = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $db;
    }
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Optional SSL verification bypass for Cloud Managed MySQL (e.g. Aiven, TiDB, Clever Cloud)
if (getenv('DB_SSL') === 'true' || getenv('MYSQL_SSL') === 'true' || $port != '3306') {
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If DB fails, output a clean user-friendly setup notification
    $error_msg = $e->getMessage();
    if (strpos($error_msg, "Unknown database") !== false) {
        die("
        <div style='font-family: Arial, sans-serif; padding: 30px; max-width: 600px; margin: 50px auto; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px;'>
            <h2 style='color: #856404; margin-top: 0;'>⚠️ Database Belum Dibuat</h2>
            <p>Database <code>".htmlspecialchars($db)."</code> tidak ditemukan di MySQL Server.</p>
            <p><strong>Langkah Penyelesaian:</strong></p>
            <ol>
                <li>Buka phpMyAdmin atau Cloud Database Console Anda.</li>
                <li>Buat database baru bernama <code>".htmlspecialchars($db)."</code>.</li>
                <li>Import berkas <code>database.sql</code> yang berada di folder proyek ini.</li>
                <li>Refresh halaman ini.</li>
            </ol>
        </div>
        ");
    } else {
        die("
        <div style='font-family: Arial, sans-serif; padding: 30px; max-width: 600px; margin: 50px auto; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;'>
            <h2 style='color: #721c24; margin-top: 0;'>❌ Koneksi Database Gagal</h2>
            <p>Gagal terhubung ke MySQL Server: <code>".htmlspecialchars($error_msg)."</code></p>
            <p>Pastikan MySQL Server (XAMPP / Cloud DB) sudah berjalan dan Environment Variables (DB_HOST, DB_USER, DB_PASS, DB_NAME) sudah sesuai.</p>
        </div>
        ");
    }
}

// Session helper start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Formatting currency helper
function format_rupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Security sanitization helper
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Load language localization helper
require_once __DIR__ . '/lang.php';
