<?php 
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Dpwd {

    public static $typeDeposit = 1;
    public static $typeWithdrawal = 2;
    public static $typeDepositNewAccount = 3;
    public static $typeRebateCommission = 4;
    public static $typeNmiCommission = 5;
    public static $typeInternalTransfer = 6;
    public static $typeWithdrawalCommission = 7;

    public static array $status = [
        "-1" => [
            'html' => '<span class="badge bg-success">Berhasil</span>',
            'text' => "Berhasil"
        ],
        "0" => [
            'html' => '<span class="badge bg-warning">Pending</span>',
            'text' => "Pending"
        ],
        "1" => [
            'html' => '<span class="badge bg-danger">Ditolak</span>',
            'text' => "Ditolak"
        ]
    ];

    public static function findByCode(string $code): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_dpwd WHERE DPWD_CODE = '{$code}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findById(string $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_dpwd WHERE MD5(MD5(tb_dpwd.ID_DPWD)) = '{$id}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findByRaccId(string $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_dpwd WHERE tb_dpwd.DPWD_RACC = '{$id}' ORDER BY tb_dpwd.ID_DPWD DESC LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function type(int $type): array {
        switch($type) {
            case self::$typeDeposit:
                return [
                    'html' => '<span class="badge bg-info">Deposit</span>',
                    'text' => "Deposit"
                ];

            case self::$typeWithdrawal:
                return [
                    'html' => '<span class="badge bg-info">Withdrawal</span>',
                    'text' => "Withdrawal"
                ];

            case self::$typeDepositNewAccount:
                return [
                    'html' => '<span class="badge bg-info">Deposit New Account</span>',
                    'text' => "Deposit New Account"
                ];

            case self::$typeRebateCommission:
                return [
                    'html' => '<span class="badge bg-info">Rebate Commission</span>',
                    'text' => "Rebate Commission"
                ];

            case self::$typeNmiCommission:
                return [
                    'html' => '<span class="badge bg-info">NMI Commission</span>',
                    'text' => "NMI Commission"
                ];

            case self::$typeInternalTransfer:
                return [
                    'html' => '<span class="badge bg-info">Internal Transfer</span>',
                    'text' => "Internal Transfer"
                ];

            case self::$typeWithdrawalCommission:
                return [
                    'html' => '<span class="badge bg-info">Withdrawal Commission</span>',
                    'text' => "Withdrawal Commission"
                ];

            default: return [
                'html' => '<span class="badge bg-dark">Invalid</span>',
                'text' => "Invalid"
            ];
        }
    }

    public static function status(int $status): array {
        switch($status) {
            case -1:
                return [
                    'html' => '<span class="badge bg-success">Berhasil</span>',
                    'badge' => 'success',
                    'text' => "Berhasil",
                    'text_en' => "Success"
                ];

            case 0:
                return [
                    'html' => '<span class="badge bg-warning">Pending</span>',
                    'badge' => 'warning',
                    'text' => "Pending",
                    'text_en' => "Pending"
                ];

            case 1:
                return [
                    'html' => '<span class="badge bg-danger">Ditolak</span>',
                    'badge' => 'danger',
                    'text' => "Ditolak",
                    'text_en' => "Rejected"
                ];
                
            default:
                return [
                    'html' => '<span class="badge bg-dark">Invalid</span>',
                    'badge' => 'dark',
                    'text' => "Invalid",
                    'text_en' => "Invalid"
                ];
        }
    }

}