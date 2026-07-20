<?php

use App\Models\Account;
use App\Models\Helper;
$login = Helper::form_input($_GET['account'] ?? 0);
$account = Account::realAccountDetail_byLogin($login);
$dt->query("
    SELECT 
        symbol,
        deal_id,
        open_time,
        action,
        volume,
        price,
        price_sl,
        price_tp
    FROM mt4_trades
    WHERE login = '{$login}' 
    AND close_time IS NULL
");

$dt->edit('action', fn($col): string => ucwords($col['action']));
$dt->edit('price', fn($col): string => Helper::formatCurrency($col['price']));

$dt->add('ACTION', function($col) {
    return '<a class="btn btn-sm btn-danger close" data-ticket="'.$col['deal_id'].'"><i class="fas fa-close text-white"></i></a>'; 
});

echo $dt->generate()->toJson();