<?php
namespace App\PaymentSystem;

use App\Models\PaymentSystem;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class BankTransfer implements PaymentSystemInterface {

    public static string $paymentCode = "bank-transfer";

    public static function detail(): array|bool {
        try {
            $db = Database::connect();
            $code = self::$paymentCode;
            $sqlGet = $db->query("SELECT * FROM tb_dpwdmethod WHERE DPWDMTH_CODE = '{$code}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function data(string $code): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_dpwd WHERE DPWD_CODE = '{$code}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }
}