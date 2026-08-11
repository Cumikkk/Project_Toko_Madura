<?php
use App\Models\Helper;
use Config\Core\Database;

if (!$adminPermissionCore->hasPermission($authorizedPermission, "/master/delete")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Anda tidak memiliki hak akses untuk menghapus master",
        'data'      => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$idUsers = intval($data['id_users'] ?? ($data['id'] ?? 0));

if ($idUsers <= 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Master tidak valid",
        'data'      => []
    ]);
    exit;
}

$db->begin_transaction();
try {
    // 1. Cari semua investor di bawah Master ini
    $investors = $db->query("SELECT id_investor, id_users FROM investor WHERE id_master = {$idUsers}");
    if ($investors && $investors->num_rows > 0) {
        while ($inv = $investors->fetch_assoc()) {
            $invId = intval($inv['id_investor']);
            $invUserId = intval($inv['id_users']);

            // Cari semua outlet di bawah investor ini
            $outlets = $db->query("SELECT id_outlet, id_users FROM outlet WHERE id_investor = {$invId}");
            if ($outlets && $outlets->num_rows > 0) {
                while ($o = $outlets->fetch_assoc()) {
                    $oId = intval($o['id_outlet']);
                    $oUserId = intval($o['id_users']);
                    $db->query("DELETE FROM laporan_omzet WHERE id_outlet = {$oId}");
                    $db->query("DELETE FROM outlet WHERE id_outlet = {$oId}");
                    if ($oUserId > 0) {
                        $db->query("DELETE FROM users WHERE id_users = {$oUserId}");
                    }
                }
            }

            $db->query("DELETE FROM investor WHERE id_investor = {$invId}");
            if ($invUserId > 0) {
                $db->query("DELETE FROM users WHERE id_users = {$invUserId}");
            }
        }
    }

    // 2. Hapus akun Master dari tabel users
    $db->query("DELETE FROM users WHERE id_users = {$idUsers} AND role = 'master'");

    $db->commit();

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil menghapus akun Master beserta seluruh data terikatnya",
        'data'      => []
    ]);
} catch (\Throwable $e) {
    $db->rollback();
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menghapus data Master: " . $e->getMessage(),
        'data'      => []
    ]);
}

