<?php
// config/database.php - Hybrid Database Connection (Local & Cloud Deployment)

// 1. Read & Clean Environment Variables for Cloud Deployment (Vercel / TiDB / Aiven / Railway)
function clean_env($key, $default = '') {
    $val = getenv($key);
    if ($val === false || $val === null || $val === '') {
        return $default;
    }
    // Strip leading/trailing spaces, quotes, and invisible characters
    return trim($val, " \t\n\r\0\x0B\"'");
}

$raw_host = clean_env('DB_HOST', clean_env('MYSQLHOST', '127.0.0.1'));
// Clean potential protocol prefixes e.g. mysql:// or https://
$raw_host = preg_replace('#^.*://#', '', $raw_host);

$port    = intval(clean_env('DB_PORT', clean_env('MYSQLPORT', '3306')));
$db      = clean_env('DB_NAME', clean_env('MYSQLDATABASE', 'coffeeshop_db'));
$user    = clean_env('DB_USER', clean_env('MYSQLUSER', 'root'));
$pass    = clean_env('DB_PASS', clean_env('MYSQLPASSWORD', ''));
$charset = 'utf8mb4';

// 2. Parse DATABASE_URL / MYSQL_URL connection string if provided
$db_url = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
if ($db_url) {
    $parsed = parse_url($db_url);
    if ($parsed) {
        $raw_host = $parsed['host'] ?? $raw_host;
        $port     = isset($parsed['port']) ? intval($parsed['port']) : $port;
        $user     = $parsed['user'] ?? $user;
        $pass     = $parsed['pass'] ?? $pass;
        $db       = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $db;
    }
}

// 3. Configure PDO Options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 7,
];

// SSL for Cloud MySQL (TiDB Cloud, Aiven, PlanetScale, Railway)
if ($port != 3306 || clean_env('DB_SSL') === 'true' || clean_env('MYSQL_SSL') === 'true') {
    if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    }
    if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

// 4. Connect with Automatic Hostname & IP Resolution Fallback
$pdo = null;
$connection_error = null;

// Primary Attempt: Connect using raw hostname
$dsn = "mysql:host={$raw_host};port={$port};dbname={$db};charset={$charset}";
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    $connection_error = $e->getMessage();

    // Fallback Attempt: If getaddrinfo failed in serverless container, resolve IP address directly
    if ($raw_host !== '127.0.0.1' && $raw_host !== 'localhost') {
        $resolved_ip = gethostbyname($raw_host);
        if ($resolved_ip && $resolved_ip !== $raw_host) {
            try {
                $fallbackDsn = "mysql:host={$resolved_ip};port={$port};dbname={$db};charset={$charset}";
                $pdo = new PDO($fallbackDsn, $user, $pass, $options);
                $connection_error = null;
            } catch (\PDOException $e2) {
                $connection_error = $e2->getMessage();
            }
        }
    }
}

// 5. Handle Failures gracefully & Set SQL Mode
if ($pdo) {
    try {
        $pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    } catch (\Exception $e) {
        // Silently ignore if provider restricts session variable modification
    }
} else {
    if (strpos($connection_error, "Unknown database") !== false) {
        die("
        <div style='font-family: Arial, sans-serif; padding: 30px; max-width: 600px; margin: 50px auto; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px;'>
            <h2 style='color: #856404; margin-top: 0;'>⚠️ Database Belum Dibuat</h2>
            <p>Database <code>".htmlspecialchars($db)."</code> tidak ditemukan di MySQL Server.</p>
            <p><strong>Langkah Penyelesaian:</strong></p>
            <ol>
                <li>Buka console database cloud Anda.</li>
                <li>Buat database baru bernama <code>".htmlspecialchars($db)."</code>.</li>
                <li>Import berkas <code>database.sql</code> yang berada di repositori ini.</li>
                <li>Refresh halaman ini.</li>
            </ol>
        </div>
        ");
    } else {
        die("
        <div style='font-family: Arial, sans-serif; padding: 30px; max-width: 600px; margin: 50px auto; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;'>
            <h2 style='color: #721c24; margin-top: 0;'>❌ Koneksi Database Gagal</h2>
            <p>Gagal terhubung ke MySQL Server: <code>".htmlspecialchars($connection_error)."</code></p>
            <p><strong>Info Host:</strong> <code>".htmlspecialchars($raw_host)."</code> (Port: ".htmlspecialchars($port).")</p>
            <p>Pastikan Environment Variables di Vercel (<code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_USER</code>, <code>DB_PASS</code>, <code>DB_NAME</code>, <code>DB_SSL=true</code>) sudah diisi dengan benar tanpa spasi.</p>
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
