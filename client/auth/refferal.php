<?php
use App\Library\Refferal\RMain;
use App\Models\Helper;
use App\Models\Refferal;
use App\Models\User;


$reffCode = Helper::form_input($_GET['b'] ?? "");
if(empty($reffCode)) {
    http_response_code(400);
    exit(json_encode(['error' => "Invalid referral code"]));
}

$isValidReffCode = RMain::refferalType($reffCode);
if(!$isValidReffCode) {
    http_response_code(400);
    exit(json_encode(['error' => "Invalid code"]));
}

$isLoggedIn = User::user();
if($isLoggedIn) {
    die("<script>location.href = '/ib/apply-referral?referral={$reffCode}';</script>");
}else {
    die("<script>location.href = '/signup?referral={$reffCode}';</script>");
}