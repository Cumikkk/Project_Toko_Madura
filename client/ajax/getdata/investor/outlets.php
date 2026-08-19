<?php
use App\Models\Investor;

$idInvestor = intval($_GET['id_investor'] ?? 0);

if ($idInvestor <= 0) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => 'ID Investor tidak valid',
        'data'    => []
    ]);
}

$data = Investor::getActiveOutletsByInvestorId($idInvestor);

JsonResponse([
    'code'    => 200,
    'success' => true,
    'message' => 'Data outlet berhasil diambil',
    'data'    => $data
]);
