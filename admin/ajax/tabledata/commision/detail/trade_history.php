<?php

use App\Library\MT5\Mt5Trades;
use App\Models\Helper;
use App\Models\Rebate;
use App\Models\User;

$userCode = Helper::form_input($_GET['code'] ?? "");
$dateStart = Helper::form_input($_GET['datestart'] ?? date("Y-m-01"));
$dateEnd = Helper::form_input($_GET['dateend'] ?? date("Y-m-t"));

/** sum total lot */
$totalLot = 0;
$userdata = User::findByCode($userCode);
if($userdata) {
    $accountTrading = Mt5Trades::getTradingAccounts_FilterByDateGroupByMemberID($dateStart, $dateEnd, $userdata['MBR_ID']);
    foreach($accountTrading as $trade) {
        $totalLot += $trade['lots'];
    }
}

$dt->query("
    SELECT
        mt5t.TICKET,
        mt5t.SYMBOL,
        mt5t.VOLUME
    FROM tb_racc ta
    JOIN tb_member tm ON (tm.MBR_ID = ta.ACC_MBR)
    JOIN (
        SELECT 
            LOGIN,
            TICKET,
            SYMBOL,
            COALESCE(VOLUME, 0) as VOLUME
        FROM mt5_trades
        WHERE LOWER(CMD) IN ('buy', 'sell')
        AND NOT EXISTS (SELECT H_TICKET FROM tb_rebate_history WHERE H_TICKET = TICKET)
        AND DATE(CLOSE_TIME) BETWEEN '$dateStart' AND '$dateEnd'
        GROUP BY TICKET
    ) as mt5t ON (mt5t.LOGIN = ta.ACC_LOGIN)
    WHERE tm.MBR_CODE = '{$userCode}'
    AND ta.ACC_DERE = 1
");

$dt->edit("VOLUME", fn($col): string => strval(floatval($col['VOLUME'])));

$array = $dt->generate()->toArray();
$array['totalLot'] = $totalLot;
$dt->generate()->toJson();

header('Content-type: application/json;');
echo json_encode($array);