<?php

use App\Library\MT5\Mt5Trades;
use App\Library\Sales\SalesMain;
use App\Models\AccountType;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\ProfilePerusahaan;
use App\Models\User;
use Config\Core\Database;


if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Permission Denied",
        'data' => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$required = [
    'datestart' => "Date Start",
    'dateend' => "Date End",
    'code' => "Code"
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "Sucecssful",
            'data' => []
        ]);
    }
}

/** validasi user code */
$userdata = User::findByCode($data['code']);
if(!$userdata) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** validasi datetime */
$dateStart = $data['datestart'];
$dateEnd = $data['dateend'];

/** Sales Type */
$salesData = SalesMain::getUserType($userdata['MBR_TYPE']);
if(!$salesData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Sales",
        'data' => []
    ]);
}

$sqlGetAllDownlines = $db->query("
    WITH RECURSIVE member_hierarchy AS (
        SELECT 
            MBR_ID,
            MBR_NAME,
            MBR_CODE,
            MBR_IDSPN,
            MBR_EMAIL,
            MBR_TYPE
        FROM tb_member
        WHERE MBR_ID = {$userdata['MBR_ID']}
        UNION ALL
        SELECT 
            m.MBR_ID,
            m.MBR_NAME,
            m.MBR_CODE,
            m.MBR_IDSPN,
            m.MBR_EMAIL,
            m.MBR_TYPE
        FROM tb_member m
        INNER JOIN member_hierarchy mh ON m.MBR_IDSPN = mh.MBR_ID
    )

    SELECT 
        mh.*,
        IFNULL(tss.SLSSTRC_NAME, 'Trader') as SALES_TYPE 
    FROM member_hierarchy mh
    LEFT JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = mh.MBR_TYPE)
    WHERE mh.MBR_ID != {$userdata['MBR_ID']}
");

$listIdDpwd = [];
$downlinesNmi = [];
$totalNMI = 0;
if($sqlGetAllDownlines) {
    foreach($sqlGetAllDownlines->fetch_all(MYSQLI_ASSOC) as $row) {
        $getNmiDownline = SalesMain::searchNmi($userdata['MBR_ID'], [$row['MBR_ID']], $dateStart, $dateEnd);
        $nmiObject = [
            'user' => $row['MBR_NAME'],
            'email' => $row['MBR_EMAIL'],
            'position' => (empty($row['ID_SLSSTRC']))? "Trader" : $row['SALES_TYPE'],
            'totalDeposit' => 0,
            'totalWithdrawal' => 0,
            'nmi' => 0
        ];

        if($getNmiDownline) {
            $listTransksi = array_merge($getNmiDownline->deposit(), $getNmiDownline->withdrawal());
            foreach($listTransksi as $trans) {
                $listIdDpwd[] = $trans['id'];
            }

            $nmiObject['totalDeposit'] = $getNmiDownline->totalDeposit();
            $nmiObject['totalWithdrawal'] = $getNmiDownline->totalWithdrawal();
            $nmiObject['nmi'] = $getNmiDownline->nmi();
        }

        $downlinesNmi[] = [
            $nmiObject['user'],
            $nmiObject['email'],
            $nmiObject['position'],
            "Rp ". Helper::formatCurrency($nmiObject['totalDeposit']),
            "Rp ". Helper::formatCurrency($nmiObject['totalWithdrawal']),
            "Rp ". Helper::formatCurrency($nmiObject['nmi']),
        ];

        $totalNMI += $nmiObject['nmi'];
    }
}

/** Target NMI */
$targetNmi = $salesData->salesDetail['SLSSTRC_NMI_TARGET'];
$percentNmi = $salesData->salesDetail['SLSSTRC_NMI_PERCENT'];
$nmiSetting = User::nmiSetting($userdata['MBR_ID']);
if($nmiSetting) {
    $targetNmi = $nmiSetting['target'];
    $percentNmi = $nmiSetting['percent'];
}

/** Share NMI */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Global Share Code */
$globalCode = "NMI".strtoupper(uniqid());

/** Insert log id DPWD */
foreach($listIdDpwd as $id) {
    $insertDpwd = Database::insert("tb_nmi_history", [
        'HNMI_MBR' => $userdata['MBR_ID'],
        'HNMI_IDDPWD' => $id,
        'HNMI_SHARECODE' => $globalCode,
    ]);

    if(!$insertDpwd) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Invalid Save 1",
            'data' => []
        ]);
    }
}

/** Insert amount nmi bonus */
if($totalNMI >= $targetNmi) {
    $rateWdBonus = ProfilePerusahaan::rateWdBonus(); 
    $amountReceived = $totalNMI * ($percentNmi / 100);
    $insertDpwd = Database::insert("tb_dpwd", [
        'DPWD_MBR' => $userdata['MBR_ID'],
        'DPWD_CODE' => $globalCode,
        'DPWD_TYPE' => Dpwd::$typeNmiCommission,
        'DPWD_AMOUNT' => $amountReceived,
        'DPWD_AMOUNT_SOURCE' => $amountReceived,
        'DPWD_CURR_FROM' => "IDR",
        'DPWD_CURR_TO' => "IDR",
        'DPWD_RATE' => 1,
        'DPWD_RATE_IDR' => $rateWdBonus,
        'DPWD_NOTE' => "Share NMI Commission ({$dateStart} - {$dateEnd})",
        'DPWD_NOTE1' => "Share NMI Commission ({$dateStart} - {$dateEnd})",
        'DPWD_STS' => -1,
        'DPWD_STSACC' => -1,
        'DPWD_STSVER' => -1,
        'DPWD_STSBC' => -1,
        'DPWD_IP' => Helper::get_ip_address(),
        'DPWD_DATETIME' => date("Y-m-d H:i:s"),
    ]);

    if(!$insertDpwd) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Jumlah yang dialokasikan tidak valid",
            'data' => []
        ]);
    }
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Shareh Bonus NMI Berhasil",
    'data' => []
]);