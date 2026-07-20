<?php
    use App\Models\Helper;
    use App\Models\Logger;
    use Config\Core\Database;
    use App\Models\Admin;

    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/password/update")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $data = Helper::getSafeInput($_POST);
    foreach(['pass01', 'pass02', 'pass03'] as $req) {
        if(empty($data[ $req ])) {
            JsonResponse([
                'code'      => 200,
                'success'   => false,
                'message'   => "Kolom {$req} diperlukan",
                'data'      => []
            ]);
        }
    }

    /** Verifikasi password admin */
    if(!password_verify($data["pass01"], $user["ADM_PASS"])) {
        JsonResponse([
            'code'  => 200,
            'success'   => false,
            'message'   => "Password salah!",
            'data'      => []
        ]);
    }

    /** Check Password baru */
    if($data["pass01"] == $data["pass02"]) {
        JsonResponse([
            'code'  => 200,
            'success'   => false,
            'message'   => "Password lama tidak bisa sama dengan password baru!",
            'data'      => []
        ]);
    }

    /** Check konfirmasi Password */
    if($data["pass02"] != $data["pass03"]) {
        JsonResponse([
            'code'  => 200,
            'success'   => false,
            'message'   => "Tolong cek kembali konfirmasi anda!",
            'data'      => []
        ]);
    }

    $UPDATE_DATA = [
        "ADM_PASS" => password_hash($data["pass03"], PASSWORD_BCRYPT)
    ];
    
    $update = Database::update("tb_admin", $UPDATE_DATA, ['ADM_ID' => $user['ADM_ID']]);
    if(!$update) {
        JsonResponse([
            'code'  => 200,
            'success'   => false,
            'message'   => "Gagal update password, silahkan coba lagi!.",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "password",
        'message' => "Mengganti password pribadi",
        'data'  => []
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update Password",
        'data'      => []
    ]);