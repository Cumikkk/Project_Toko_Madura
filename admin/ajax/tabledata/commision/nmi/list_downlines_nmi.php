<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\User;

$userCode = Helper::form_input($_GET['code'] ?? "");
$dateStart = Helper::form_input($_GET['datestart'] ?? date("Y-m-01"));
$dateEnd = Helper::form_input($_GET['dateend'] ?? date("Y-m-t"));

/** Get list of downlines mbrid */
$userdata = User::findByCode($userCode);
if(!$userdata) {
    exit(json_encode([]));
}

/** Sales Type */
$salesData = SalesMain::getUserType($userdata['MBR_TYPE']);
if(!$salesData) {
    exit(json_encode([]));
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
$estimatedAcquisition = "Belum memenuhi target (Rp ".Helper::formatCurrency($targetNmi).")";
if($nmiSetting) {
    $targetNmi = $nmiSetting['target'];
    $percentNmi = $nmiSetting['percent'];
}

if($totalNMI >= $targetNmi) {
    $estimatedAcquisition = "Rp ".Helper::formatCurrency($totalNMI * ($percentNmi / 100)) . " ({$percentNmi}%)";
}

exit(json_encode([
    'data' => $downlinesNmi,
    'draw' => ($_GET['draw'] ?? 1),
    'recordsFiltered' => count($downlinesNmi),
    'recordsTotal' => count($downlinesNmi),
    'totalNMI' => "Rp ".Helper::formatCurrency($totalNMI),
    'estimatedAcquisition' => $estimatedAcquisition
]));