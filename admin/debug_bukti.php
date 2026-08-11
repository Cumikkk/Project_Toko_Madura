<?php
require_once __DIR__ . '/../config/setting.php';
use Config\Core\Database;

$db = Database::connect();
$res = $db->query("SELECT id_outlet, nama_outlet, status, bukti_pembayaran FROM outlet WHERE bukti_pembayaran IS NOT NULL AND bukti_pembayaran != ''");

// Build URL same way as admin outlet view.php
$protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host       = $_SERVER['HTTP_HOST'] ?? 'localhost';
$docRoot    = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$curDir     = rtrim(str_replace('\\', '/', __DIR__), '/');
$relDir     = str_replace($docRoot, '', $curDir);
$parts      = array_values(array_filter(explode('/', $relDir)));
$projectDir = count($parts) > 0 ? '/' . $parts[0] : '';
$clientBaseUrl = $protocol . $host . $projectDir . '/client';

header('Content-Type: text/plain');
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_NAME:   " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "docRoot (norm): " . $docRoot . "\n";
echo "curDir (norm):  " . $curDir . "\n";
echo "relDir:         " . $relDir . "\n";
echo "parts[0]:       " . ($parts[0] ?? 'N/A') . "\n";
echo "clientBaseUrl:  " . $clientBaseUrl . "\n";
echo "\n=== Outlets with bukti_pembayaran ===\n";
while($row = $res->fetch_assoc()) {
    $fileUrl = $clientBaseUrl . '/' . ltrim($row['bukti_pembayaran'], '/');
    echo "Outlet: " . $row['nama_outlet'] . "\n";
    echo "  DB bukti: " . $row['bukti_pembayaran'] . "\n";
    echo "  Full URL: " . $fileUrl . "\n";
    // Check if file exists on disk
    $filePath = $docRoot . $projectDir . '/client/' . $row['bukti_pembayaran'];
    echo "  Disk path: " . $filePath . "\n";
    echo "  File exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "\n\n";
}
