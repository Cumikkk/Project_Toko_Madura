<?php

use App\Library\MT5\Mt5Trades;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\Rebate;
use Config\Core\SystemInfo;

$topUser = 1000000000;
$dateStart = Helper::form_input($_GET['start'] ?? null);
$dateEnd = Helper::form_input($_GET['end'] ?? null);
$result = [];

try {
    $accountTrading = Mt5Trades::getTradingAccounts_FilterByDateGroupByMemberID($dateStart, $dateEnd);
    foreach($accountTrading as $row) {
        $result[] = [
            'id' => $row['code'],
            'pid' => 0,
            'login' => $row['logins'],
            'fullname' => $row['name'],
            'email' => $row['email'],
            'lot' => Helper::formatCurrency($row['lots'], 2)
        ];
    }

    exit(json_encode($result));

} catch (Exception $e) {
    if(SystemInfo::isDevelopment()) {
        throw $e;
    }

    exit(json_encode([]));
}