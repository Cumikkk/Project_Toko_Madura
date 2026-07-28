<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$idUsers = intval($data['id_users'] ?? 0);

if ($idUsers <= 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Master tidak valid",
        'data'      => []
    ]);
}

$db->query("DELETE FROM users WHERE id_users = {$idUsers} AND role = 'master'");

if ($db->affected_rows > 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil menghapus akun Master Owner",
        'data'      => []
    ]);
} else {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menghapus data Master Owner atau data tidak ditemukan",
        'data'      => []
    ]);
}
