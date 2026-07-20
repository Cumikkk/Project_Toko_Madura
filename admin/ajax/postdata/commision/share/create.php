<?php

use App\Library\MT5\Mt5Trades;
use App\Library\Sales\SalesMain;
use App\Models\AccountType;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\Logger;
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

/** get head of sales info */
$headOfSales = SalesMain::findMyHeadOfSales($userdata['MBR_ID']);
$salesCommission = SalesMain::salesCommission($headOfSales->memberData['MBR_ID']);

if(!$headOfSales) {
    JsonResponse([
        'success' => false,
        'message' => "This user does not have a rebate commission settings",
        'data' => []
    ]);
}

if(!$salesCommission) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Commission",
        'data' => []
    ]);
}

/** List uplines user, untuk mencari upline berdasarkan type nya agar tidak query berulang" */
$uplines = Ib::getNetworks($userdata['MBR_ID'], "upline");

/** List account trading group by product */
$accountTrading = Mt5Trades::getTradingAccounts_FilterByDateGroupByProduct($dateStart, $dateEnd, $userdata['MBR_ID']);

/** Variable untuk menampung list user penerima rebate */
$listUserPenerimaRebate = [];
$listTicket = [];
$totalLot = 0;

/** Prosess perhitaungan rebate */
foreach($accountTrading as $acc) {
    $product = AccountType::findById($acc['id_product']);
    $commissionSetting = $salesCommission->commissionSetting($acc['id_product'])->get();
    $tradingLot = $acc['lots'];
    $listTicket = array_merge($listTicket, explode(",", $acc['tickets']));
    $totalLot += $tradingLot;

    if($product) {
        foreach($commissionSetting['settings'] as $setting) {
            $searchUplineCommission = array_search($setting['id'], array_column($uplines, 'MBR_TYPE'));
            $totalUsd = 0;
            if($searchUplineCommission !== false) {
                $uplineCommission = $uplines[ $searchUplineCommission ]; 
                if($uplineCommission['MBR_ID'] == $userdata['MBR_ID']) {
                    /** Skip jika member id == diri sendiri */
                    continue;
                }

                foreach($setting['amounts'] as $amount) {
                    $lot = 0;
                    if($acc['symbol']['id'] == $amount['category_id']) {
                        $lot = $acc['lots'];
                    }

                    $amountBonus = $amount['amount'] * $lot;
                    $totalUsd += $amountBonus; 
                }

                $listUserPenerimaRebate[] = [
                    'mbrid' => $uplineCommission['MBR_ID'],
                    'name' => $uplineCommission['MBR_NAME'],
                    'code' => $uplineCommission['MBR_CODE'],
                    'amount' => $totalUsd,
                    'desc' => "Rebate Commission dari " . $userdata['MBR_NAME'] . " " . $amount['category_name'] . " $"."{$totalUsd} Periode {$dateStart} - {$dateEnd}"
                ];
            }
        }
    }
}


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Pembuatan kode rebate global */
$kodeRebate = ("RBT".strtoupper(uniqid()));

/** insert ke history rebate */
foreach($listTicket as $ticket) {
    $insertHistoryRebate = Database::insert("tb_rebate_history", [
        'H_TICKET' => $ticket,
        'H_MBR' => $userdata['MBR_ID'],
        'H_CODE' => $kodeRebate,
        'H_AMOUNT' => $totalLot,
    ]);

    if(!$insertHistoryRebate) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Gagal menyimpan log ticket {$ticket}",
            'data' => []
        ]);
    }
}

/** insert ke dpwd */
foreach($listUserPenerimaRebate as $penerima) {
    $insertDpwd = Database::insert("tb_dpwd", [
        'DPWD_MBR' => $penerima['mbrid'],
        'DPWD_CODE' => $kodeRebate,
        'DPWD_TYPE' => Dpwd::$typeRebateCommission,
        'DPWD_AMOUNT' => $penerima['amount'],
        'DPWD_AMOUNT_SOURCE' => $penerima['amount'],
        'DPWD_CURR_FROM' => "USD",
        'DPWD_CURR_TO' => "USD",
        'DPWD_RATE' => 1,
        'DPWD_NOTE' => $penerima['desc'],
        'DPWD_NOTE1' => $penerima['desc'],
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
            'message' => "Share rebate failed on user {$penerima['name']}",
            'data' => []
        ]);
    }
}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "Share Rebate",
    'message' => "Share Rebate",
    'data'  => [
        'kode' => $kodeRebate
    ]
]);

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Share rebate berhasil",
    'data' => []
]);