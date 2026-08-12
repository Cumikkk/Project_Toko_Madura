<?php
use App\Models\Helper;
use App\Models\Investor;

if (!$adminPermissionCore->hasPermission($authorizedPermission, "/investor/delete")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Anda tidak memiliki hak akses untuk menghapus investor",
        'data'      => []
    ]);
    exit;
}

$data = Helper::getSafeInput($_POST);
$idInvestor = intval($data['id_investor'] ?? ($data['id'] ?? 0));

$result = Investor::deleteInvestor($idInvestor);

JsonResponse([
    'code'      => 200,
    'success'   => $result['success'],
    'message'   => $result['message'],
    'data'      => []
]);
