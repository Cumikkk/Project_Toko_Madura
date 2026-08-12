<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\Helper;

$db = Database::connect();
$userId = (int) $user['MBR_ID'];

// Validate input
$nama = Helper::form_input($_POST['nama_lengkap'] ?? '');
$username = Helper::form_input($_POST['username'] ?? '');
$noHp = Helper::form_input($_POST['no_hp'] ?? '');
$kecamatan = Helper::form_input($_POST['kecamatan'] ?? '');
$alamat = Helper::form_input($_POST['alamat_lengkap'] ?? '');
$password = Helper::form_input($_POST['password'] ?? '');

if (empty($nama) || empty($username)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nama lengkap dan Username tidak boleh kosong!'
    ]);
    exit;
}

// Check username uniqueness (if changed)
$sqlCheck = $db->prepare("SELECT id_users FROM users WHERE username = ? AND id_users != ?");
$sqlCheck->bind_param("si", $username, $userId);
$sqlCheck->execute();
$resCheck = $sqlCheck->get_result();
if ($resCheck->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Username sudah digunakan oleh akun lain.'
    ]);
    exit;
}
$sqlCheck->close();

$db->begin_transaction();
try {
    if (!empty($password)) {
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama_lengkap = ?, username = ?, no_hp = ?, kecamatan = ?, alamat_lengkap = ?, password = ? WHERE id_users = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssssi", $nama, $username, $noHp, $kecamatan, $alamat, $hashedPassword, $userId);
    } else {
        $sql = "UPDATE users SET nama_lengkap = ?, username = ?, no_hp = ?, kecamatan = ?, alamat_lengkap = ? WHERE id_users = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssi", $nama, $username, $noHp, $kecamatan, $alamat, $userId);
    }

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data profil.");
    }
    
    // Update session data if needed
    $_SESSION['nama_lengkap'] = $nama;
    $_SESSION['username'] = $username;

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Data profil berhasil diperbarui.'
    ]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
