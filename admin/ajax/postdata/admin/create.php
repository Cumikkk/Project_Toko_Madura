<?php
use App\Models\Helper;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/admin/create")) {
    JsonResponse([
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['add-fullname', 'add-username', 'add-password'] as $req) {
    if(empty($data[ $req ])) {
        $req = str_replace("add-", "", $req);
        JsonResponse([
            'success'   => false,
            'message'   => "{$req} diperlukan",
            'data'      => []
        ]);
    }
}

$add_fullname   = $data['add-fullname'];
$add_username   = $data['add-username'];
$add_password   = $data['add-password'];

// Check username in users table
$sql_check_username = $db->query("SELECT id_users FROM users WHERE LOWER(username) = LOWER('".$db->real_escape_string($add_username)."') LIMIT 1");
if(!$sql_check_username || $sql_check_username->num_rows != 0) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username sudah digunakan",
        'data'      => []
    ]);
}

if(!preg_match('/^[a-zA-Z0-9]+$/', $add_username)) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh huruf dan angka (tanpa spasi)",
        'data'      => []
    ]);
}

// Check password strength
$check_password = Helper::validation_password($add_password);
if($check_password !== TRUE) {
    JsonResponse([
        'success'   => false,
        'message'   => $check_password,
        'data'      => []
    ]);
}

$add_email = !empty($data['add-email']) ? $data['add-email'] : null;
$add_phone = !empty($data['add-phone']) ? $data['add-phone'] : null;

// Insert into users table
$insert = Database::insert("users", [
    'nama_lengkap' => $add_fullname,
    'username'     => $add_username,
    'email'        => $add_email,
    'no_hp'        => $add_phone,
    'password'     => password_hash($add_password, PASSWORD_BCRYPT),
    'role'         => 'master'
]);

if(!$insert) {
    JsonResponse([
        'success'   => false,
        'message'   => "Gagal membuat admin baru",
        'data'      => []
    ]);
}

$newAdminId = $db->insert_id;
$add_level = intval($data['add-level'] ?? 2);

// Auto-assign permissions based on level
$permSql = "SELECT id, module_id FROM admin_permissions";
$sqlPerms = $db->query($permSql);
if($sqlPerms && $sqlPerms->num_rows > 0) {
    while($pRow = $sqlPerms->fetch_assoc()) {
        $pId = $pRow['id'];
        $modId = $pRow['module_id'];
        
        $shouldGrant = false;
        if ($add_level == 1) {
            // Programmer: FULL
            $shouldGrant = true;
        } elseif ($add_level == 2) {
            // Master: Dashboard, Investor, Pengaturan, Admin (module_id 1..4)
            if (in_array($modId, [1, 2, 3, 4])) {
                $shouldGrant = true;
            }
        } elseif ($add_level == 3) {
            // Admin Staf: Dashboard, Investor, Pengaturan (module_id 1..3)
            if (in_array($modId, [1, 2, 3])) {
                $shouldGrant = true;
            }
        }

        if ($shouldGrant) {
            Database::insert("admin_authorize", [
                'admin_id' => $newAdminId,
                'permission_id' => $pId,
                'status' => -1
            ]);
        }
    }
}

JsonResponse([
    'success'   => true,
    'message'   => "Berhasil menambahkan admin baru",
    'data'      => [
        'redirect' => \Config\Core\SystemInfo::app('ADMIN_URL') . "/admin/view"
    ]
]);