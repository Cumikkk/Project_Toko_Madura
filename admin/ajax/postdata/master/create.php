<?php
use App\Models\Helper;
use App\Models\Master;
use Config\Core\SystemInfo;

$data = Helper::getSafeInput($_POST);
$result = Master::saveMaster($data);

if ($result["success"]) {
    $result["data"] = ["redirect" => SystemInfo::app("ADMIN_URL") . "/master/view"];
}

JsonResponse([
    "code"      => 200,
    "success"   => $result["success"],
    "message"   => $result["message"],
    "data"      => $result["data"] ?? []
]);

