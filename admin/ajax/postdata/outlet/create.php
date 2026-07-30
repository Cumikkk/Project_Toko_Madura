<?php
use App\Models\Helper;
use Config\Core\Database;
use Config\Core\SystemInfo;

$data     = Helper::getSafeInput($_POST);
$idOutlet = intval($data['id_outlet'] ?? 0);
$isEdit   = ($idOutlet > 0);

// Permission check
$requiredPerm = $isEdit ? "/outlet/update" : "/outlet/create";
if (!$adminPermissionCore->hasPermission($authorizedPermission, $requiredPerm) && !$adminPermissionCore->hasPermission($authorizedPermission, "/outlet/create")) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Anda tidak memiliki hak akses untuk mengubah/menambah outlet toko",
        'data'    => []
    ]);
    exit;
}

// Collect fields
$nama_outlet         = trim($data['nama_outlet']         ?? '');
$id_investor         = intval($data['id_investor']       ?? 0);
$kecamatan           = trim($data['kecamatan']           ?? '');
$alamat_outlet       = trim($data['alamat_outlet']       ?? '');
$persentase_potongan = floatval(str_replace(',', '.', $data['persentase_potongan'] ?? '10.00'));
$kasir_nama          = trim($data['kasir_nama']          ?? '');
$kasir_username      = trim($data['kasir_username']      ?? '');
$kasir_password      = trim($data['kasir_password']      ?? '');
$kasir_no_hp         = trim($data['kasir_no_hp']         ?? '');
$idUsersKasir        = intval($data['id_users_kasir']    ?? 0);

// Validate required fields
if (empty($nama_outlet) || empty($id_investor) || empty($kasir_nama) || empty($kasir_username)) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Semua kolom utama (Nama Toko, Investor, Nama Kasir, Username Kasir) wajib diisi",
        'data'    => []
    ]);
    exit;
}

if (!$isEdit && empty($kasir_password)) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Password kasir wajib diisi saat mendaftar outlet baru",
        'data'    => []
    ]);
    exit;
}

if (!empty($kasir_password)) {
    $check_password = Helper::validation_password($kasir_password);
    if ($check_password !== true) {
        JsonResponse([
            'code'    => 200,
            'success' => false,
            'message' => $check_password,
            'data'    => []
        ]);
        exit;
    }
}

// Escape strings
$namaSafe      = $db->real_escape_string($nama_outlet);
$kecamatanSafe = $db->real_escape_string($kecamatan);
$alamatSafe    = $db->real_escape_string($alamat_outlet);
$kasirNama     = $db->real_escape_string($kasir_nama);
$kasirUser     = $db->real_escape_string($kasir_username);
$kasirHp       = $db->real_escape_string($kasir_no_hp);

if ($isEdit) {
    // =========== EDIT MODE ===========

    // Check outlet exists
    $resOut = $db->query("SELECT id_outlet, id_users FROM outlet WHERE id_outlet = {$idOutlet} LIMIT 1");
    if (!$resOut || $resOut->num_rows == 0) {
        JsonResponse([
            'code'    => 200,
            'success' => false,
            'message' => "Data cabang toko tidak ditemukan",
            'data'    => []
        ]);
        exit;
    }
    $outletRow    = $resOut->fetch_assoc();
    $existingUser = intval($outletRow['id_users']);

    // Check kasir username uniqueness (exclude current kasir user)
    $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$kasirUser}') AND id_users != {$existingUser} LIMIT 1");
    if ($chkUser && $chkUser->num_rows > 0) {
        JsonResponse([
            'code'    => 200,
            'success' => false,
            'message' => "Username kasir '{$kasir_username}' sudah digunakan oleh akun lain",
            'data'    => []
        ]);
        exit;
    }

    // Update kasir user
    if (!empty($kasir_password)) {
        $hashedPass = password_hash($kasir_password, PASSWORD_BCRYPT);
        $hashSafe   = $db->real_escape_string($hashedPass);
        $db->query("UPDATE users SET nama_lengkap = '{$kasirNama}', username = '{$kasirUser}', no_hp = '{$kasirHp}', password = '{$hashSafe}' WHERE id_users = {$existingUser}");
    } else {
        $db->query("UPDATE users SET nama_lengkap = '{$kasirNama}', username = '{$kasirUser}', no_hp = '{$kasirHp}' WHERE id_users = {$existingUser}");
    }

    // Update outlet
    $db->query("UPDATE outlet SET nama_outlet = '{$namaSafe}', id_investor = {$id_investor}, kecamatan = '{$kecamatanSafe}', persentase_potongan = {$persentase_potongan}, alamat_outlet = '{$alamatSafe}' WHERE id_outlet = {$idOutlet}");

    JsonResponse([
        'code'    => 200,
        'success' => true,
        'message' => "Berhasil memperbarui data outlet dan akun kasir: {$nama_outlet}",
        'data'    => ['redirect' => SystemInfo::app('ADMIN_URL') . "/outlet/view"]
    ]);

} else {
    // =========== CREATE MODE ===========

    // Check kasir username uniqueness
    $chkUser = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('{$kasirUser}') LIMIT 1");
    if ($chkUser && $chkUser->num_rows > 0) {
        JsonResponse([
            'code'    => 200,
            'success' => false,
            'message' => "Username kasir '{$kasir_username}' sudah digunakan oleh akun lain",
            'data'    => []
        ]);
        exit;
    }

    // 1. Insert kasir user (role = 'outlet')
    $hashedPass = password_hash($kasir_password, PASSWORD_BCRYPT);
    $hashSafe   = $db->real_escape_string($hashedPass);
    $db->query("INSERT INTO users (nama_lengkap, username, no_hp, password, role) VALUES ('{$kasirNama}', '{$kasirUser}', '{$kasirHp}', '{$hashSafe}', 'outlet')");

    if ($db->affected_rows < 1) {
        JsonResponse([
            'code'    => 200,
            'success' => false,
            'message' => "Gagal membuat akun kasir baru: " . $db->error,
            'data'    => []
        ]);
        exit;
    }

    $newKasirId = $db->insert_id;

    // 2. Insert outlet with persentase_potongan, tanggal_request, and tanggal_disetujui
    $db->query("INSERT INTO outlet (id_users, id_investor, nama_outlet, kecamatan, persentase_potongan, alamat_outlet, status, tanggal_request, tanggal_disetujui) VALUES ({$newKasirId}, {$id_investor}, '{$namaSafe}', '{$kecamatanSafe}', {$persentase_potongan}, '{$alamatSafe}', 'active', NOW(), NOW())");

    if ($db->affected_rows < 1) {
        // Rollback: delete the kasir user we just created
        $db->query("DELETE FROM users WHERE id_users = {$newKasirId}");
        JsonResponse([
            'code'    => 200,
            'success' => false,
            'message' => "Gagal mendaftarkan cabang outlet baru: " . $db->error,
            'data'    => []
        ]);
        exit;
    }

    JsonResponse([
        'code'    => 200,
        'success' => true,
        'message' => "Berhasil mendaftarkan toko cabang baru beserta akun kasir: {$nama_outlet}",
        'data'    => ['redirect' => SystemInfo::app('ADMIN_URL') . "/outlet/view"]
    ]);
}
