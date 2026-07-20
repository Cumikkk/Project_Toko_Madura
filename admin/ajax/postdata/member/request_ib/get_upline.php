<?php

use App\Models\Helper;
use App\Models\User;

$userCode = Helper::form_input($_POST['code'] ?? "");
$userData = User::findByCode($userCode);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid user",
        'data' => []
    ]);
}

/** Get All Users */
$sqlGetUser = $db->query("
    SELECT 
        tm.MBR_ID, 
        tm.MBR_CODE,
        tm.MBR_EMAIL,
        tm.MBR_TYPE,
        tss.SLSSTRC_NAME
    FROM tb_member tm
    LEFT JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = tm.MBR_TYPE) 
    WHERE tm.MBR_STS = -1
    AND tm.MBR_VERIF = -1 
    AND tm.MBR_ID NOT IN (1000000000, ".$userData['MBR_ID'].") 
");

$result = [];
foreach($sqlGetUser->fetch_all(MYSQLI_ASSOC) as $userr) {
    $result[] = [
        'code' => $userr['MBR_CODE'],
        'email' => $userr['MBR_EMAIL'],
        'selected' => ($userr['MBR_ID'] == $userData['MBR_IDSPN']),
        'type' => $userr['MBR_TYPE'],
        'type_name' => $userr['SLSSTRC_NAME'] ?? "Trader",
    ];
}

JsonResponse([
    'success' => true,
    'message' => "Success",
    'data' => $result
]);