<?php
use App\Models\Helper;
use App\Models\Admin;
use App\Models\Country;
use App\Models\Logger;
use Config\Core\Database;

$listGrup = $adminPermissionCore->availableGroup();
$adminRoles = Admin::adminRoles();
if(!$adminPermissionCore->hasPermission($authorizedPermission, "/admin/create")) {
    JsonResponse([
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
foreach(['add-fullname', 'add-username', 'add-password', 'add-level', 'add-country'] as $req) {
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
$add_level      = $data['add-level'];
$add_token      = $data['add-token'] ?? "-";
$country        = $data['add-country'];

/** Check username */
$sql_check_username = $db->query("SELECT ADM_USER FROM tb_admin WHERE LOWER(ADM_USER) = LOWER('".$add_username."') LIMIT 1");
if(!$sql_check_username || mysqli_num_rows($sql_check_username) != 0) {
    JsonResponse([
        'success'   => false,
        'message'   => "Invalid Username",
        'data'      => []
    ]);
}

if(!preg_match('/^[a-zA-Z0-9]+$/', $add_username)) {
    JsonResponse([
        'success'   => false,
        'message'   => "Username tidak valid, hanya boleh string(tanpa spasi)",
        'data'      => []
    ]);
}

/** validasi password */
$check_password = Helper::validation_password($add_password);
if($check_password !== TRUE) {
    JsonResponse([
        'success'   => false,
        'message'   => $check_password,
        'data'      => []
    ]);
}

/** validasi role */
if(!in_array($add_level, array_column($adminRoles, "ID_ADMROLE"))) {
    JsonResponse([
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
        'success'   => false,
        'message'   => "Invalid Country",
        'data'      => []
    ]);
}

$userCountry = $countries[ $search ];
$insert = Database::insert("tb_admin", [
    'ADM_ID'    => Admin::createAdmId(),
    'ADM_USER'  => $add_username,
    'ADM_NAME'  => $add_fullname,
    'ADM_COUNTRY' => ($userCountry['ID_COUNTRY'] ?? 7),
    'ADM_PASS'  => password_hash($add_password, PASSWORD_BCRYPT),
    'ADM_IP'  => Helper::get_ip_address(),
    'ADM_LEVEL'  => $add_level,
    'ADM_STS'  => -1
]);

if(!$insert) {
    JsonResponse([
        'success'   => false,
        'message'   => "Failed create admin",
        'data'      => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "admin",
    'message' => "Menambahkan admin baru: {$add_username}",
    'data'  => $data
]);

JsonResponse([
    'success'   => true,
    'message'   => "create admin successfully",
    'data'      => [
        'redirect' => "/admin/view"
    ]
]);