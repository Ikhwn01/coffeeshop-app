<?php
// api/index.php - Vercel Serverless PHP Request Dispatcher & Router

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

// Serve static assets directly if hit
$assetPath = __DIR__ . '/../' . ltrim($uri, '/');
if ($uri !== '/' && file_exists($assetPath) && !is_dir($assetPath) && !str_ends_with($assetPath, '.php')) {
    return false;
}

// 1. Admin Routes (/admin/...)
if (preg_match('#^/admin(?:/(.*))?$#', $uri, $matches)) {
    $page = !empty($matches[1]) ? $matches[1] : 'index.php';
    if (!str_ends_with($page, '.php')) {
        $page .= '.php';
    }
    $targetFile = __DIR__ . '/../admin/' . $page;
    if (file_exists($targetFile)) {
        require $targetFile;
        exit;
    }
    require __DIR__ . '/../admin/index.php';
    exit;
}

// 2. API Routes (/api/...)
if (preg_match('#^/api/(.+)$#', $uri, $matches)) {
    $apiFile = $matches[1];
    if (!str_ends_with($apiFile, '.php')) {
        $apiFile .= '.php';
    }
    $targetFile = __DIR__ . '/' . $apiFile;
    if (file_exists($targetFile)) {
        require $targetFile;
        exit;
    }
}

// 3. Main Frontend Pages
$cleanUri = trim($uri, '/');
if (empty($cleanUri)) {
    require __DIR__ . '/../index.php';
    exit;
}

if (!str_ends_with($cleanUri, '.php')) {
    $cleanUri .= '.php';
}

$targetFile = __DIR__ . '/../' . $cleanUri;
if (file_exists($targetFile)) {
    require $targetFile;
    exit;
}

// Fallback to homepage
require __DIR__ . '/../index.php';
