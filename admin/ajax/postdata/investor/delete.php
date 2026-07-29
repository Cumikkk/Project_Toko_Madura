<?php
use App\Models\Helper;
use Config\Core\Database;

if (!$adminPermissionCore->hasPermission($authorizedPermission, "/investor/delete")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Anda tidak memiliki hak akses untuk menghapus investor",
        'data'      => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$idInvestor = intval($data['id_investor'] ?? ($data['id'] ?? 0));

if ($idInvestor <= 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Investor tidak valid",
        'data'      => []
    ]);
    exit;
}

$resInv = $db->query("SELECT id_users FROM investor WHERE id_investor = {$idInvestor} LIMIT 1");
if (!$resInv || $resInv->num_rows == 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Data investor tidak ditemukan",
        'data'      => []
    ]);
    exit;
}

$userId = intval($resInv->fetch_assoc()['id_users']);

$db->begin_transaction();
try {
    // 1. Hapus seluruh outlet beserta laporan_omzet dan user kasirnya
    $outlets = $db->query("SELECT id_outlet, id_users FROM outlet WHERE id_investor = {$idInvestor}");
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

    // 2. Hapus rekap bagi hasil investor ini
    $db->query("DELETE FROM rekap_bagi_hasil WHERE id_investor = {$idInvestor}");

    // 3. Hapus data investor
    $db->query("DELETE FROM investor WHERE id_investor = {$idInvestor}");

    // 4. Hapus user investor
    if ($userId > 0) {
        $db->query("DELETE FROM users WHERE id_users = {$userId}");
    }

    $db->commit();

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Data investor beserta outlet terikat berhasil dihapus",
        'data'      => []
    ]);
} catch (\Throwable $e) {
    $db->rollback();
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menghapus data investor: " . $e->getMessage(),
        'data'      => []
    ]);
}

