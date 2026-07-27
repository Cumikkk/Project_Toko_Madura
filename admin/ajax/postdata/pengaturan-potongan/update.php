<?php
use App\Models\Helper;
use Config\Core\Database;

$permission = $adminPermissionCore->hasPermission($authorizedPermission, "/pengaturan-potongan/update");
if (!$permission && !$adminPermissionCore->hasPermission($authorizedPermission, "/pengaturan/update")) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Authorization Failed",
        'data'    => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$potongan = floatval($data['potongan_global'] ?? 0);

if ($potongan < 0 || $potongan > 100) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Persentase potongan global harus di antara 0% hingga 100%",
        'data'    => []
    ]);
    exit;
}

// Upsert setting into pengaturan_sistem
$db->query("
    INSERT INTO pengaturan_sistem (nama_pengaturan, nilai) 
    VALUES ('potongan_global', {$potongan}) 
    ON DUPLICATE KEY UPDATE nilai = {$potongan}
");

JsonResponse([
    'code'    => 200,
    'success' => true,
    'message' => "Persentase potongan global omzet berhasil diperbarui menjadi " . number_format($potongan, 2, ',', '.') . "%",
    'data'    => []
]);
