<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\Product;
use App\Models\SalesStructure;
use App\Models\Symbols;

/** check amount */
$amount = Helper::form_input($_POST['amount'] ?? 0);
$amount = Helper::stringTonumber($amount);
if(is_numeric($amount) === FALSE || $amount < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Amount",
        'data' => []
    ]);
}

/** check symbol */
$symbol = Helper::form_input($_POST['symbol'] ?? "");
$symbolDetail = Symbols::findByNameCategory($symbol);
if(!$symbolDetail) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Symbol Category",
        'data' => []
    ]);
}

/** check product */
$productSuffix = Helper::form_input($_POST['product'] ?? "");
$product = Product::findBySuffix($productSuffix);
if(!$product) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Product",
        'data' => []
    ]);
}

/** Sales Commission */
$salesCommission = SalesMain::salesCommission($user['MBR_ID']);
if(!$salesCommission) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Commission",
        'data' => []
    ]);
}


/** check sales code */
$amounts = [];
$sales = $_POST['sales'] ?? [];
$commissionSetting = $salesCommission->commissionSetting($product['ID_RTYPE'])->get();
foreach($commissionSetting['settings'] as $setting) {
    $salesAmount = Helper::stringTonumber(($sales[ $setting['code'] ] ?? -1));
    if(is_numeric($salesAmount) === FALSE || $salesAmount < 0) {
        JsonResponse([
            'success' => false,
            'message' => "Kolom {$setting['code']} tidak valid",
            'data' => []
        ]);
    }

    $amounts[] = [
        'id' => $setting['id'],
        'level' => $setting['level'],
        'code' => $setting['code'],
        'amount' => $salesAmount
    ];
}

/** summary */
$maxAmount = $salesCommission->max($symbolDetail['ID_SYMCAT']);
$allocatedAmount = $salesCommission->sum($symbolDetail['ID_SYMCAT']);

/** check max amount */
if(array_sum(array_column($amounts, "amount")) != $maxAmount) {
    JsonResponse([
        'success' => false,
        'message' => "Jumlah yang harus dialokasikan adalah {$maxAmount}",
        'data' => []
    ]);
}

/** update amount */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

foreach($amounts as $sls) {
    $addRebateSetting = $salesCommission->addOrUpdate($symbolDetail['ID_SYMCAT'], $product['ID_RTYPE'], $sls['id'], $sls['level'], $sls['amount']);
    if(!$addRebateSetting) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Gagal mengalokasikan jumlah {$sls['amount']} ke {$sls['code']}",
            'data' => []
        ]);
    }
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);