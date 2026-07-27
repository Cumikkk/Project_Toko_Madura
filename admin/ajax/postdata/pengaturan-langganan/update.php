<?php
use App\Models\Helper;
use Config\Core\Database;

$permission = $adminPermissionCore->hasPermission($authorizedPermission, "/pengaturan-langganan/update");
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
$biaya = floatval($data['biaya_langganan_outlet'] ?? 0);

if ($biaya < 0) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Nominal biaya langganan tidak boleh bernilai negatif",
        'data'    => []
    ]);
    exit;
}

// Upsert setting into pengaturan_sistem
$db->query("
    INSERT INTO pengaturan_sistem (nama_pengaturan, nilai) 
    VALUES ('biaya_langganan_outlet', {$biaya}) 
    ON DUPLICATE KEY UPDATE nilai = {$biaya}
");

JsonResponse([
    'code'    => 200,
    'success' => true,
    'message' => "Biaya langganan outlet berhasil diperbarui menjadi Rp " . number_format($biaya, 0, ',', '.'),
    'data'    => []
]);
