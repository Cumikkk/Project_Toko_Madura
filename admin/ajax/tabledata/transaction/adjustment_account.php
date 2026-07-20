<?php

$dt->query("
    SELECT
        ADJUST_ACC_DATETIME,
        ADJUST_ACC_LOGIN,
        ADJUST_ACC_TYPE,
        ADJUST_ACC_TICKET,
        ADJUST_ACC_AMOUNT,
        ADJUST_ACC_COMMENT
    FROM tb_adjustment_account taa
");

$dt->edit('ADJUST_ACC_TYPE', function($col) {
    return App\Models\AdjustmentAccount::typeHtml($col['ADJUST_ACC_TYPE']);
});

$dt->edit('ADJUST_ACC_AMOUNT', function($col) {
    return '$' . App\Models\Helper::formatCurrency($col['ADJUST_ACC_AMOUNT'], 2);
});

echo $dt->generate()->toJson();