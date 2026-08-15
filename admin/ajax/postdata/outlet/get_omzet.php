<?php
use App\Models\Helper;
use App\Models\Outlet;

$data = Helper::getSafeInput($_POST);
$idOutlet = intval($data['id_outlet'] ?? 0);
$bulan    = intval($data['bulan'] ?? 0);
$tahun    = intval($data['tahun'] ?? 0);

if ($idOutlet <= 0) {
    JsonResponse([
        'code'    => 400,
        'success' => false,
        'message' => 'ID Outlet tidak valid',
        'data'    => []
    ]);
    exit;
}

$result = Outlet::getOutletOmzetDetail($idOutlet, $bulan, $tahun);

JsonResponse([
    'code'      => 200,
    'success'   => true,
    'outlet'    => $result['outlet'],
    'transaksi' => $result['transaksi'],
    'summary'   => $result['summary']
]);
exit;
