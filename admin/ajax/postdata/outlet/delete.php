<?php
use App\Models\Helper;
use Config\Core\Database;
use Config\Core\SystemInfo;

if (!$adminPermissionCore->hasPermission($authorizedPermission, "/outlet/delete")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Anda tidak memiliki hak akses untuk menghapus cabang outlet",
        'data'      => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$idOutlet = intval($data['id_outlet'] ?? ($data['id'] ?? 0));

if ($idOutlet <= 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Outlet tidak valid",
        'data'      => []
    ]);
    exit;
}

$resOutlet = $db->query("SELECT id_users, bukti_pembayaran FROM outlet WHERE id_outlet = {$idOutlet} LIMIT 1");
if (!$resOutlet || $resOutlet->num_rows == 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Data outlet tidak ditemukan",
        'data'      => []
    ]);
    exit;
}
$rowOutlet = $resOutlet->fetch_assoc();
$userId = intval($rowOutlet['id_users'] ?? 0);
$buktiPembayaran = trim($rowOutlet['bukti_pembayaran'] ?? '');

$db->begin_transaction();
try {
    // 1. Hapus laporan_omzet
    $db->query("DELETE FROM laporan_omzet WHERE id_outlet = {$idOutlet}");
    // 2. Hapus data outlet
    $db->query("DELETE FROM outlet WHERE id_outlet = {$idOutlet}");
    // 3. Hapus akun kasir di users
    if ($userId > 0) {
        $db->query("DELETE FROM users WHERE id_users = {$userId}");
    }

    // 4. Hapus fisik file bukti_pembayaran dari server jika ada
    if (!empty($buktiPembayaran)) {
        $path1 = WEB_ROOT . '/' . $buktiPembayaran;
        $path2 = CRM_ROOT . '/' . $buktiPembayaran;
        if (file_exists($path1)) {
            @unlink($path1);
        }
        if (file_exists($path2)) {
            @unlink($path2);
        }
    }

    $db->commit();

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil menghapus toko cabang outlet",
        'data'      => [
            'redirect' => SystemInfo::app('ADMIN_URL') . "/outlet/view"
        ]
    ]);
} catch (\Throwable $e) {
    $db->rollback();
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menghapus cabang outlet: " . $e->getMessage(),
        'data'      => []
    ]);
}

