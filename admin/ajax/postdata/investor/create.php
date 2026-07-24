<?php
use App\Models\Helper;
use Config\Core\Database;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);
$idInvestor = intval($data['id_investor'] ?? 0);
$isEdit = ($idInvestor > 0);

$requiredPerm = $isEdit ? "/investor/update" : "/investor/create";
if (!$adminPermissionCore->hasPermission($authorizedPermission, $requiredPerm) && !$adminPermissionCore->hasPermission($authorizedPermission, "/investor/create")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$nama_lengkap = $data['nama_lengkap'] ?? '';
$username     = $data['username'] ?? '';
$password     = $data['password'] ?? '';
$email        = !empty($data['email']) ? $data['email'] : null;
$no_hp        = !empty($data['no_hp']) ? $data['no_hp'] : null;
$alamat       = !empty($data['alamat_investor']) ? $data['alamat_investor'] : null;
$persenRaw = str_replace(',', '.', $data['persen_bagian_investor'] ?? '60.0');
$persen    = floatval($persenRaw);

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
        'message'   => "Password wajib diisi untuk investor baru",
        'data'      => []
    ]);
}

if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)",
        'data'      => []
    ]);
}

if ($isEdit) {
    // 1. Edit Mode
    $resInv = $db->query("SELECT id_users FROM investor WHERE id_investor = {$idInvestor} LIMIT 1");
    if (!$resInv || $resInv->num_rows == 0) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Data investor tidak ditemukan",
            'data'      => []
        ]);
    }
    $userId = intval($resInv->fetch_assoc()['id_users']);

    // Username uniqueness check excluding current user
    $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('".$db->real_escape_string($username)."') AND id_users != {$userId} LIMIT 1");
    if ($sql_check && $sql_check->num_rows > 0) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Username '{$username}' sudah digunakan oleh pengguna lain",
            'data'      => []
        ]);
    }

    // Update users table
    if (!empty($password)) {
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $db->query("UPDATE users SET nama_lengkap = '".$db->real_escape_string($nama_lengkap)."', username = '".$db->real_escape_string($username)."', email = ".($email ? "'".$db->real_escape_string($email)."'" : "NULL").", no_hp = ".($no_hp ? "'".$db->real_escape_string($no_hp)."'" : "NULL").", password = '".$db->real_escape_string($hashedPass)."' WHERE id_users = {$userId}");
    } else {
        $db->query("UPDATE users SET nama_lengkap = '".$db->real_escape_string($nama_lengkap)."', username = '".$db->real_escape_string($username)."', email = ".($email ? "'".$db->real_escape_string($email)."'" : "NULL").", no_hp = ".($no_hp ? "'".$db->real_escape_string($no_hp)."'" : "NULL")." WHERE id_users = {$userId}");
    }

    // Update investor table
    $db->query("UPDATE investor SET alamat_investor = ".($alamat ? "'".$db->real_escape_string($alamat)."'" : "NULL").", persen_bagian_investor = {$persen} WHERE id_investor = {$idInvestor}");

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil memperbarui data investor: {$nama_lengkap}",
        'data'      => [
            'redirect' => SystemInfo::app('ADMIN_URL') . "/investor/view"
        ]
    ]);

} else {
    // 2. Create Mode
    $sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('".$db->real_escape_string($username)."') LIMIT 1");
    if ($sql_check && $sql_check->num_rows > 0) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Username '{$username}' sudah terdaftar, silakan pilih username lain",
            'data'      => []
        ]);
    }

    $insertUser = Database::insert("users", [
        'nama_lengkap' => $nama_lengkap,
        'username'     => $username,
        'email'        => $email,
        'no_hp'        => $no_hp,
        'password'     => password_hash($password, PASSWORD_BCRYPT),
        'role'         => 'investor'
    ]);

    if (!$insertUser) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Gagal membuat akun user investor",
            'data'      => []
        ]);
    }

    $newUserId = $db->insert_id;
    $masterId  = $user['ADM_ID'] ?? 1;

    $insertInvestor = Database::insert("investor", [
        'id_users'               => $newUserId,
        'id_master'              => $masterId,
        'alamat_investor'        => $alamat,
        'persen_bagian_investor' => $persen
    ]);

    if (!$insertInvestor) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Gagal menyimpan data profil investor",
            'data'      => []
        ]);
    }

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Berhasil mendaftarkan investor baru: {$nama_lengkap}",
        'data'      => [
            'redirect' => SystemInfo::app('ADMIN_URL') . "/investor/view"
        ]
    ]);
}
