<?php
use App\Models\Helper;
use App\Models\Outlet;

$data  = Helper::getSafeInput($_POST);
$bulan = intval($data['bulan'] ?? 0);
$tahun = intval($data['tahun'] ?? 0);

$result = Outlet::getOutletOmzetMonitoring($bulan, $tahun);

JsonResponse([
    'code'    => 200,
    'success' => true,
    'outlets' => $result['outlets'] ?? [],
    'summary' => $result['summary'] ?? []
]);
exit;
