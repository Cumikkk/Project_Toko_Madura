<?php
use App\Models\Helper;
use Config\Core\Database;
use Config\Core\SystemInfo;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/admin/investor/create")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['nama_lengkap', 'username', 'password', 'persen_bagian_investor'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Kolom {$req} diperlukan",
            'data'      => []
        ]);
    }
}

$nama_lengkap = $data['nama_lengkap'];
$username     = $data['username'];
$password     = $data['password'];
$email        = !empty($data['email']) ? $data['email'] : null;
$no_hp        = !empty($data['no_hp']) ? $data['no_hp'] : null;
$alamat       = !empty($data['alamat_investor']) ? $data['alamat_investor'] : null;
$persen       = floatval($data['persen_bagian_investor']);

// Check username uniqueness in users table
$sql_check = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('".$db->real_escape_string($username)."') LIMIT 1");
if($sql_check && $sql_check->num_rows > 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Username sudah terdaftar, silakan pilih username lain",
        'data'      => []
    ]);
}

if(!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)",
        'data'      => []
    ]);
}

// 1. Insert into users table (role = 'investor')
$insertUser = Database::insert("users", [
    'nama_lengkap' => $nama_lengkap,
    'username'     => $username,
    'email'        => $email,
    'no_hp'        => $no_hp,
    'password'     => password_hash($password, PASSWORD_BCRYPT),
    'role'         => 'investor'
]);

if(!$insertUser) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal membuat akun user investor",
        'data'      => []
    ]);
}

$newUserId = $db->insert_id;
$masterId  = $user['ADM_ID'] ?? 1;

// 2. Insert into investor table
$insertInvestor = Database::insert("investor", [
    'id_users'               => $newUserId,
    'id_master'              => $masterId,
    'alamat_investor'        => $alamat,
    'persen_bagian_investor' => $persen
]);

if(!$insertInvestor) {
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
