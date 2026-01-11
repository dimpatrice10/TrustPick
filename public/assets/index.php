<?php
// Lightweight asset proxy: serves files from ../assets/ when DocumentRoot is `public/`
// Usage: /assets/<path> -> serves file from ../assets/<path>

// Only allow GET/HEAD
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'])) {
    http_response_code(405);
    header('Allow: GET, HEAD');
    exit;
}

$f = isset($_GET['f']) ? $_GET['f'] : '';
// Prevent NUL bytes and directory traversal
$f = str_replace("\0", '', $f);
$f = ltrim($f, '/');

// Resolve file in project root /assets
$root = dirname(__DIR__, 2); // two levels up from public/assets -> project root
$assetsDir = $root . DIRECTORY_SEPARATOR . 'assets';
$filePath = realpath($assetsDir . DIRECTORY_SEPARATOR . $f);

if ($filePath === false || strpos($filePath, realpath($assetsDir)) !== 0 || !is_file($filePath)) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

// Determine mime type
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $filePath);
    finfo_close($finfo);
} else {
    $mime = mime_content_type($filePath) ?: 'application/octet-stream';
}

// Basic caching headers
$etag = md5_file($filePath);
header('ETag: "' . $etag . '"');
header('Cache-Control: public, max-age=86400');
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    readfile($filePath);
}
exit;
