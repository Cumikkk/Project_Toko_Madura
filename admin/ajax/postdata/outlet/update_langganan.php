<?php
use App\Models\Helper;
use App\Models\Outlet;

$data = Helper::getSafeInput($_POST);
$idInvestor = intval($data['id_investor'] ?? 0);
$biayaBaru  = intval(str_replace(['.', ',', ' '], '', $data['biaya_langganan_outlet'] ?? 0));

if ($idInvestor <= 0) {
    JsonResponse([
        'code'    => 400,
        'success' => false,
        'message' => 'ID Investor tidak valid'
    ]);
    exit;
}

$result = Outlet::updateBiayaLanggananInvestor($idInvestor, $biayaBaru);

JsonResponse([
    'code'    => 200,
    'success' => $result['success'],
    'message' => $result['message']
]);
exit;
