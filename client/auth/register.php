<?php

use App\Models\AccountType;
use App\Factory\UtmHandler;
use App\Models\Helper;
use App\Models\Refferal;
use App\Models\User;
use Config\Core\SystemInfo;

/** Save UTM Data to session */
UtmHandler::saveUtmToCookie(UtmHandler::extractUtmData());

/** Refferal Code */
$refferalCode = Helper::form_input($_GET['rc'] ?? "");
if(empty($refferalCode)) {
    die("<script>alert('Invalid Refferal Code'); location.href = '/signup';</script>");
}

/** Jika ada refferalCode di MBR_CODE, maka menggunakan refferal code all */
$reffLength = strlen($refferalCode);
$user = User::findByCode($refferalCode);
if($user) {
    $refferalLink = SystemInfo::app('CLIENT_URL') . "/signup?referral=" . $refferalCode;
    die("<script>location.href = '$refferalLink'; </script>");

} else {
    /** Jika tidak ada, maka pecah refferal code, product suffix selali 2 digit */
    $userCode = substr($refferalCode, 0, -2);
    
    /** Check user ulang dengan refferal code yang sudah dipecah */
    $user = User::findByCode($userCode);
    if(!$user) {
        die("<script>alert('Invalid Refferal'); location.href = '/signup';</script>");
    }
}

/** Pecah product suffix dari refferal code */
$productSuffix = substr($refferalCode, ($reffLength - 2));
if(empty($productSuffix)) {
    die("<script>alert('Invalid 2'); location.href = '/signup';</script>");
}

/** Search Product Suffix */
$product = AccountType::findBySuffix($productSuffix);
if(!$product) {
    die("<script>alert('Invalid 3'); location.href = '/signup';</script>");
}

$refferalLink = SystemInfo::app('CLIENT_URL') . "/signup?referral=" . implode("-", [$product['RTYPE_SUFFIX'], Refferal::parseProductType($product['RTYPE_TYPE']), $user['MBR_CODE']]);
die("<script>location.href = '$refferalLink'; </script>");