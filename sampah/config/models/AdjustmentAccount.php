<?php
namespace App\Models;

class AdjustmentAccount {

    public static string $typeDeposit = 'deposit';
    public static string $typeWithdrawal = 'withdrawal';

    public static function typeHtml(string $type) {
        if($type === self::$typeDeposit) {
            return '<span class="badge bg-success">Deposit</span>';
        }
        
        if($type === self::$typeWithdrawal) {
            return '<span class="badge bg-danger">Withdrawal</span>';
        } 

        return '<span class="badge bg-dark">Unknown</span>';
    }

    public static function comment() {
        return [
            'Adjustment Account',
            'Deposit',
            'Withdrawal',
        ];
    }

}