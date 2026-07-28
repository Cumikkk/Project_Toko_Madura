<?php
use App\Models\Helper;
use Config\Core\Database;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);
$idUsers = intval($data['id_users'] ?? 0);
$isEdit  = ($idUsers > 0);

$nama_lengkap = trim($data['nama_lengkap'] ?? '');
$username     = trim($data['username'] ?? '');
$password     = trim($data['password'] ?? '');
$no_hp        = !empty($data['no_hp']) ? trim($data['no_hp']) : null;

if (empty($nama_lengkap) || empty($username)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Nama Lengkap dan Username wajib diisi",
        'data'      => []
    ]);
}

if (!$isEdit && empty($password)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Password wajib diisi untuk Master baru",
        'data'      => []
    ]);
}

if (!empty($password)) {
    $check_password = Helper::validation_password($password);
    if ($check_password !== true) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => $check_password,
            'data'      => []
        ]);
    }
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)",
        'data'      => []
    ]);
}

$nameSafe     = $db->real_escape_string($nama_lengkap);
$usernameSafe = $db->real_escape_string($username);
$hpVal        = $no_hp ? "'" . $db->real_escape_string($no_hp) . "'" : "NULL";

if ($isEdit) {
    // 1. Edit Mode
    $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$usernameSafe}') AND id_users != {$idUsers} LIMIT 1");
    if ($sql_check && $sql_check->num_rows > 0) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Username '{$username}' sudah digunakan oleh pengguna lain",
            'data'      => []
        ]);
    }

    if (!empty($password)) {
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $passSafe   = $db->real_escape_string($hashedPass);
        $db->query("UPDATE users SET nama_lengkap = '{$nameSafe}', username = '{$usernameSafe}', no_hp = {$hpVal}, password = '{$passSafe}' WHERE id_users = {$idUsers} AND role = 'master'");
    } else {
        $db->query("UPDATE users SET nama_lengkap = '{$nameSafe}', username = '{$usernameSafe}', no_hp = {$hpVal} WHERE id_users = {$idUsers} AND role = 'master'");
    }

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil memperbarui data Master: {$nama_lengkap}",
        'data'      => [
            'redirect' => SystemInfo::app('ADMIN_URL') . "/master/view"
        ]
    ]);

} else {
    // 2. Create Mode
    $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$usernameSafe}') LIMIT 1");
    if ($sql_check && $sql_check->num_rows > 0) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Username '{$username}' sudah terdaftar, silakan pilih username lain",
            'data'      => []
        ]);
    }

    $hashedPass = password_hash($password, PASSWORD_BCRYPT);
    $passSafe   = $db->real_escape_string($hashedPass);

    $db->query("INSERT INTO users (nama_lengkap, username, no_hp, password, role) VALUES ('{$nameSafe}', '{$usernameSafe}', {$hpVal}, '{$passSafe}', 'master')");

    if ($db->affected_rows < 1) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Gagal membuat akun Master: " . $db->error,
            'data'      => []
        ]);
    }

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil mendaftarkan Master baru: {$nama_lengkap}",
        'data'      => [
            'redirect' => SystemInfo::app('ADMIN_URL') . "/master/view"
        ]
    ]);
}
