<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\User;
use App\Models\Helper;

header('Content-Type: application/json');

$db = Database::connect();
$user = User::user();

if (!$user || strtolower($user['role'] ?? '') !== 'master') {
    JsonResponse([
        'code'      => 403,
        'success'   => false,
        'message'   => "Akses ditolak. Hanya Master Owner yang dapat menambahkan investor.",
        'data'      => []
    ]);
}

$masterUserId = intval($user['MBR_ID'] ?? $user['id_users'] ?? 0);
if ($masterUserId <= 0) {
    JsonResponse([
        'code'      => 400,
        'success'   => false,
        'message'   => "Sesi user tidak valid.",
        'data'      => []
    ]);
}

$data         = $_POST;
$nama_lengkap = trim($data['nama_lengkap'] ?? '');
$username     = trim($data['username'] ?? '');
$password     = trim($data['password'] ?? '');
$no_hp        = !empty($data['no_hp']) ? trim($data['no_hp']) : null;
$kecamatan    = !empty($data['kecamatan']) ? trim($data['kecamatan']) : null;
$alamat       = !empty($data['alamat_investor']) ? trim($data['alamat_investor']) : null;

if (empty($nama_lengkap) || empty($username) || empty($password)) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Nama Lengkap, Username, dan Password wajib diisi",
        'data'      => []
    ]);
}

$check_password = Helper::validation_password($password);
if ($check_password !== true) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => $check_password,
        'data'      => []
    ]);
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
$kecVal       = $kecamatan ? "'" . $db->real_escape_string($kecamatan) . "'" : "NULL";
$alamatVal    = $alamat ? "'" . $db->real_escape_string($alamat) . "'" : "NULL";

// Check username uniqueness
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

// Insert into users table
$db->query("INSERT INTO users (nama_lengkap, username, no_hp, password, role) VALUES ('{$nameSafe}', '{$usernameSafe}', {$hpVal}, '{$passSafe}', 'investor')");

if ($db->affected_rows < 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal membuat akun user investor: " . $db->error,
        'data'      => []
    ]);
}

$newUserId = $db->insert_id;

// Insert into investor table linked to master
$db->query("INSERT INTO investor (id_users, id_master, kecamatan, alamat_investor, persen_bagian_investor, tanggal_bergabung) VALUES ({$newUserId}, {$masterUserId}, {$kecVal}, {$alamatVal}, 50.00, NOW())");

if ($db->affected_rows < 1) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Gagal menyimpan data investor: " . $db->error,
        'data'      => []
    ]);
}

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "Berhasil mendaftarkan investor baru: {$nama_lengkap}",
    'data'      => [
        'redirect' => SystemInfo::app('CLIENT_URL') . "/investor"
    ]
]);
