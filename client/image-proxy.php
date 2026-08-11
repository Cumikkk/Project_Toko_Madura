<?php
/**
 * Image proxy endpoint untuk menampilkan bukti pembayaran dari client/uploads/ atau admin/uploads/
 * Diakses via: /client/image-proxy.php?file=uploads/bukti_pembayaran/filename.jpg
 */
require_once __DIR__ . '/../config/setting.php';

$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$file = $_GET['file'] ?? '';

// Sanitasi: cegah path traversal
$file = ltrim($file, '/');
$file = str_replace(['..', '\\', '//'], '', $file);

if (empty($file)) {
    http_response_code(400);
    exit('Invalid request');
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExtensions)) {
    http_response_code(400);
    exit('File type not allowed');
}

// Hanya izinkan file dari folder uploads/bukti_pembayaran/ atau uploads/bukti_komisi/
if (strpos($file, 'uploads/bukti_pembayaran/') !== 0 && strpos($file, 'uploads/bukti_komisi/') !== 0) {
    http_response_code(403);
    exit('Access denied');
}

// projectRoot (parent of client/)
$projectRoot = dirname(__DIR__);
$filePath = $projectRoot . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR
          . str_replace('/', DIRECTORY_SEPARATOR, $file);

if (!file_exists($filePath) || !is_file($filePath)) {
    $filePath = $projectRoot . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR
              . str_replace('/', DIRECTORY_SEPARATOR, $file);
}

if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit('File not found: ' . $filePath);
}

$mimeTypes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
];

header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=3600');
readfile($filePath);
exit;
