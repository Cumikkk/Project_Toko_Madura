<?php
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Country;
use App\Models\Logger;
use Config\Core\Database;

$listGrup = $adminPermissionCore->availableGroup();
$adminRoles = Admin::adminRoles();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/admin/update/*")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['admin_id', 'fullname', 'username', 'level', 'country'] as $req) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Kolom {$req} diperlukan",
            'data'      => []
        ]);
    }
}

$admin_id = $data['admin_id'];
$fullname = $data['fullname'];
$username = $data['username'];
$level = $data['level'];
$token = $data['token'] ?? "-";
$country = $data['country'];

/** check admin id */
if(is_numeric($admin_id) === FALSE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak valid",
        'data'      => []
    ]);
}

$admin = Admin::findById($admin_id);
if(!$admin) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "ID Admin tidak ditemukan",
        'data'      => []
    ]);
}

if(!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh string(tanpa spasi)",
        'data'      => []
    ]);
}

/** Check username */
$db = Database::connect();
$sql_check_username = $db->query("SELECT * FROM tb_admin WHERE LOWER(ADM_USER) = LOWER('{$username}') AND ID_ADM != {$admin_id} LIMIT 1");
if($sql_check_username->num_rows != 0) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Username sudah terdaftar",
        'data'      => []
    ]);
}

/** validasi role */
if(!in_array($level, array_column($adminRoles, "ID_ADMROLE"))) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid level",
        'data'      => []
    ]);
}

/** Validasi country */
$countries = Country::countries();
$search = array_search($country, array_column($countries, "COUNTRY_NAME"));
if($search === FALSE) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Invalid Country",
        'data'      => []
    ]);
}

$userCountry = $countries[ $search ];
$updateData = [
    'ADM_USER'  => $username,
    'ADM_NAME'  => $fullname,
    'ADM_COUNTRY' => ($userCountry['ID_COUNTRY'] ?? 7),
    'ADM_IP'  => Helper::get_ip_address(),
    'ADM_LEVEL'  => $level,
    'ADM_STS'  => -1
];

$update = Database::update("tb_admin", $updateData, ['ADM_ID' => $admin['ADM_ID']]);
if(!$update) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Failed create admin",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "admin",
    'message' => "Memperbarui data admin {$username}",
    'data'  => $data
]);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'message'   => "update admin successfully",
    'data'      => [
        'redirect' => "/admin/view"
    ]
]);