<?php

use App\Models\Account;
use App\Models\Helper;
$login = Helper::form_input($_GET['account'] ?? 0);
$account = Account::realAccountDetail_byLogin($login);
$dt->query("
    SELECT 
        OPEN_TIME,
        deal_id,
        symbol,
        action,
        volume,
        price,
        price_sl,
        price_tp,
        profit
    FROM mt4_trades
    WHERE login = '{$login}' 
    AND `time` IS NOT NULL
");

$dt->edit('action', fn($col): string => ucwords($col['action']));
$dt->edit('price', fn($col): string => Helper::formatCurrency($col['price']));


echo $dt->generate()->toJson();