<?php
use App\Models\Helper;
use App\Models\Outlet;

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

$result = Outlet::saveOutlet($data, $user['ADM_ID'] ?? 1);
if ($result['success']) {
    $result['data'] = ['redirect' => \Config\Core\SystemInfo::app('ADMIN_URL') . "/outlet/view"];
}

JsonResponse(array_merge(['code' => 200], $result));
exit;
