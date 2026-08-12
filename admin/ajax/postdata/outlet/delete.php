<?php
use App\Models\Helper;
use App\Models\Outlet;

if (!$adminPermissionCore->hasPermission($authorizedPermission, "/outlet/delete")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Anda tidak memiliki hak akses untuk menghapus cabang outlet",
        'data'      => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$idOutlet = intval($data['id_outlet'] ?? ($data['id'] ?? 0));

$result = Outlet::deleteOutlet($idOutlet);
if ($result['success']) {
    $result['data'] = ['redirect' => \Config\Core\SystemInfo::app('ADMIN_URL') . "/outlet/view"];
}

JsonResponse(array_merge(['code' => 200], $result));
exit;
