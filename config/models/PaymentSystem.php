<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class PaymentSystem {

    public static function activeDeposit() {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_dpwdmethod WHERE DPWDMTH_DEPOSIT != 1");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function activeWithdrawal() {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_dpwdmethod WHERE DPWDMTH_WITHDRAWAL != 1");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

}