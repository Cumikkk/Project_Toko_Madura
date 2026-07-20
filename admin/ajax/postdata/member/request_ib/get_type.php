<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\SalesDivision;
use App\Models\SalesStructure;
use App\Models\User;

/** List structure */
$sqlGetSalesStructure = $db->query("SELECT * FROM tb_sales_structure JOIN tb_sales_division ON (ID_SLSDIVISION = SLSSTRC_DIV) ORDER BY ID_SLSSTRC ASC");
$salesStructure = [];
foreach($sqlGetSalesStructure->fetch_all(MYSQLI_ASSOC) as $ar) {
    $salesStructure[] = [
        'code' => $ar['SLSSTRC_CODE'],
        'name' => $ar['SLSSTRC_NAME'],
        'division' => $ar['ID_SLSDIVISION'],
        'division_name' => $ar['SLSDIVISION_NAME'],
        'level' => $ar['SLSSTRC_LEVEL']
    ];
}

$userCode = Helper::form_input($_POST['code'] ?? "");
$userData = User::findByCode($userCode);

/** Jika tidak mempunyai Upline */
if(!$userData || ($userData && $userData['MBR_ID'] == 1000000000)) {
    JsonResponse([
        'success' => true,
        'message' => "No Upline",
        'data' => array_reduce($salesStructure, function($carry, $ar) {
            $carry[] = [
                'code' => $ar['code'],
                'name' => $ar['name'],
                'division_name' => $ar['division_name']
            ];

            return $carry;
        })
    ]);
}

/** Jika mempunyai upline dan bukan perusahaan */
$userLevel = 0;
$userDivision = 0;
$salesData = SalesMain::getUserType($userData['MBR_TYPE']);
if($salesData) {
    $division = $salesData->division();
    if($division) {
        $userLevel = $salesData->level();
        $userDivision = $division['ID_SLSDIVISION'];
    }
}

/** Jika upline tidak mempunyai divisi  */
if($userDivision == 0) {
    JsonResponse([
        'success' => true,
        'message' => "Upline No Division",
        'data' => array_reduce($salesStructure, function($carry, $ar) {
            if($ar['level'] > 0) {
                $carry[] = [
                    'code' => $ar['code'],
                    'name' => $ar['name'],
                    'division_name' => $ar['division_name']
                ];
            }

            return $carry;
        })
    ]);
}

/** Jika upline mempunyai divisi */
if($userDivision != 0) {
    JsonResponse([
        'success' => true,
        'message' => "Upline With Division",
        'data' => array_reduce($salesStructure, function($carry, $ar) use ($userDivision, $userLevel) {
            if($userDivision == $ar['division'] && $ar['level'] > $userLevel) {
                $carry[] = [
                    'code' => $ar['code'],
                    'name' => $ar['name'],
                    'division_name' => $ar['division_name']
                ];
            }
    
            return $carry;
        })
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Structure",
    'data' => []
]);