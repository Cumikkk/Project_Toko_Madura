<?php

use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\AccountCategory;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\Product;
use Config\Core\Database;

if(!$adminPermissionCore->hasPermission($authorizedPermission, "/tools/product/update/1")) {
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Authorization Failed",
        'data'      => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$required = [
    'code' => "Code",
    'suffix' => "Suffix",
    'name' => "Name",
    'type' => "Type",
    'rate' => "Rate",
    'type_as' => "SPA/MULTI",
    'leverage' => "Leverage",
    'group' => "Group",
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} field is required",
            'data' => []
        ]);
    }
}

/** check numeric */
$numericField = [
    'minimum_deposit' => "Minimum Deposit",
    'maximum_deposit' => "Maximum Deposit",
    'minimum_topup' => "Minimum Topup",
    'maximum_topup' => "Maximum Topup",
    'minimum_withdrawal' => "Minimum Withdrawal",
    'maximum_withdrawal' => "Maximum Withdrawal",
];

foreach($numericField as $field => $text) {
    $numeric = Helper::stringTonumber($data[ $field ]);
    if(is_numeric($numeric) === FALSE) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} must be numeric",
            'data' => []
        ]);
    }

    if($numeric < 0) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} must be greater than or equal to 0",
            'data' => []
        ]);
    }
}

/** validasi suffix & code */
$product = Product::findById($data['code']);
if(!$product) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);

}

$data['suffix'] = strtolower(preg_replace('/[^a-zA-Z0-9]/', "", $data['suffix']));
$accountSuffix = Product::findBySuffix($data['suffix']);
if($accountSuffix) {
    if($accountSuffix['ID_RTYPE'] != $product['ID_RTYPE']) {
        JsonResponse([
            'success' => false,
            'message' => "Suffix already used",
            'data' => []
        ]);
    }
}

/** validasi type */
$data['type'] = strtoupper($data['type']);
// if(!in_array($data['type'], Product::$type)) {
//     JsonResponse([
//         'success' => false,
//         'message' => "Invalid Type, must be " . implode(", ", Product::$type),
//         'data' => []
//     ]);
// }

/** validasi type as */
$data['type_as'] = strtoupper($data['type_as']);
if(!in_array($data['type_as'], Product::$typeAs)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type, must be " . implode(", ", Product::$typeAs),
        'data' => []
    ]);
}

/** validasi rate */
$data['rate'] = strtoupper($data['rate']);
if(!in_array($data['rate'], Product::$rates)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Rate",
        'data' => []
    ]);
}

/** validasi max account */
if(is_numeric($data['max_account']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Max Account",
        'data' => []
    ]);
}

/** validasi komisi */
$commission = Helper::stringTonumber($data['commission']);
if(is_numeric($commission) === FALSE && $commission < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Commission",
        'data' => []
    ]);
}

/** validasi leverage */
$leverage = Helper::stringTonumber($data['leverage']);
if(is_numeric($leverage) === FALSE && $leverage <= 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Leverage",
        'data' => []
    ]);
}

/** validasi minimum margin */
$minimumMargin = Helper::stringTonumber($data['minimum_deposit']);
if(is_numeric($minimumMargin) === FALSE && $minimumMargin < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Minimum Deposit",
        'data' => []
    ]);
}

/** validasi maximum margin */
$maximumMargin = Helper::stringTonumber($data['maximum_deposit']);
if(is_numeric($maximumMargin) === FALSE && $maximumMargin < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Maximum Deposit",
        'data' => []
    ]);
}

/** validasi minimum deposit */
$minimumDeposit = Helper::stringTonumber($data['minimum_topup']);
if(is_numeric($minimumDeposit) === FALSE && $minimumDeposit < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Minimum Deposit",
        'data' => []
    ]);
}

/** validasi maximum deposit */
$maximumDeposit = Helper::stringTonumber($data['maximum_topup']);
if(is_numeric($maximumDeposit) === FALSE && $maximumDeposit < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Maximum Deposit",
        'data' => []
    ]);
}

/** validasi minimum withdrawal */
$minimumWithdrawal = Helper::stringTonumber($data['minimum_withdrawal']);
if(is_numeric($minimumWithdrawal) === FALSE && $minimumWithdrawal < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Minimum Withdrawal",
        'data' => []
    ]);
}

/** validasi maximum withdrawal */
$maximumWithdrawal = Helper::stringTonumber($data['maximum_withdrawal']);
if(is_numeric($maximumWithdrawal) === FALSE && $maximumWithdrawal < 0) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Maximum Withdrawal",
        'data' => []
    ]);
}

/** validasi status */
$status = $data['status'] ?? "-1";
if(!in_array($status, array_keys(Product::$status))) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status",
        'data' => []
    ]);
}

/**validasi sales model */
if(is_numeric($data['sales_model']) === FALSE) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid value of Sales Model",
        'data' => []
    ]);
}

$accountCategory = AccountCategory::findById($data['sales_model']);
if(!$accountCategory) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Sales Model",
        'data' => []
    ]);
}

/** validasi currency */
$rate = $data['rate'] == "FLOATING"? 0 : $data['rate'];
$isFloating = $rate == 0? 1 : 0;

$currency = !empty($data['currency'])? strtoupper($data['currency']) : (($isFloating)? "USD" : "IDR");
$currencySymbol = ($currency == "IDR")? "Rp" : "$";
if(!in_array($currency, Product::$currency)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Currency",
        'data' => []
    ]);
}

/** Update Product */
$prefix = $data['prefix'] ?? 0;
$updateData = [
    'RTYPE_CAT' => $accountCategory['ID_RCAT'],
    'RTYPE_PREFIX' => $prefix,
    'RTYPE_SUFFIX' => $data['suffix'],
    'RTYPE_NAME' => $data['name'],
    'RTYPE_TYPE_AS' => $data['type_as'],
    'RTYPE_TYPE' => str_replace(" ", "-", $data['type']),
    'RTYPE_SWAP' => $data['swap'],
    'RTYPE_RATE' => $rate,
    'RTYPE_MAXACC' => $data['max_account'],
    'RTYPE_ISFLOATING' => $isFloating,
    'RTYPE_CURR' => $currency,
    'RTYPE_CURR_SYMBOL' => $currencySymbol,
    'RTYPE_MINDEPOSIT' => $minimumMargin,
    'RTYPE_MAXDEPOSIT' => $maximumMargin,
    'RTYPE_MINTOPUP' => $minimumDeposit,
    'RTYPE_MAXTOPUP' => $maximumDeposit,
    'RTYPE_MINWITHDRAWAL' => $minimumWithdrawal,
    'RTYPE_MAXWITHDRAWAL' => $maximumWithdrawal,
    'RTYPE_LEVERAGE' => $leverage,
    'RTYPE_KOMISI' => $commission,
    'RTYPE_GROUP' => $data['group'],
    'RTYPE_STS' => $status,
];

/** validasi trading rules */
if(!empty($_FILES['trading_rule']) && $_FILES['trading_rule']['error'] == 0) {
    $uploadFile = FileUploadFactory::aws()->upload_pdf($_FILES['trading_rule']);
    if(!is_array($uploadFile) || !array_key_exists("filename", $uploadFile)) {
        JsonResponse([
            'success' => false,
            'message' => $uploadFile ?? "Gagal upload trading rule",
            'data' => []
        ]);
    }

    $updateData['RTYPE_FILE'] = $uploadFile['filename'];
}

$updateProduct = Database::update("tb_racctype", $updateData, ['ID_RTYPE' => $product['ID_RTYPE']]);
if(!$updateProduct) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to update product",
        'data' => []
    ]);
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "update-product",
    'message' => "Update Product",
    'data'  => [
        'before' => $product,
        'after' => $updateData,
        'file' => $uploadFile ?? []
    ]
]);

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => [
        'redirect' => "/tools/product/view"
    ]
]);