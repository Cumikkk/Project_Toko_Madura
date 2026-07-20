<?php
namespace App\Library\MT5;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class MT5Balance {

    public static function getAccountBalance(int $login): array|bool {
        try {
            $dbmetalive = SystemInfo::app('DB_METALIVE');
            $dbmetademo = SystemInfo::app('DB_METADEMO');
            
            $db = Database::connect();
            $sql = $db->query("
                SELECT 
                    IFNULL(mt4_users_real.balance, mt4_users_demo.balance) as balance,
                    IFNULL(mt4_users_real.equity, mt4_users_demo.equity) as equity,
                    IFNULL(mt4_users_real.margin, mt4_users_demo.margin) as margin,
                    IFNULL(mt4_users_real.margin_free, mt4_users_demo.margin_free) as margin_free,
                    IFNULL(mt4_users_real.margin_level, mt4_users_demo.margin_level) as margin_level
                FROM tb_racc
                LEFT JOIN {$dbmetalive}.MT4_USERS mt4_users_real ON (mt4_users_real.login = tb_racc.ACC_LOGIN)
                LEFT JOIN {$dbmetademo}.MT4_USERS mt4_users_demo ON (mt4_users_demo.login = tb_racc.ACC_LOGIN)
                WHERE ACC_LOGIN = {$login}
            ");

            if($sql->num_rows != 1) {
                return false;
            }

            $row = $sql->fetch_assoc();
            return [
                'balance' => floatval($row['balance']),
                'equity' => floatval($row['equity']),
                'margin' => floatval($row['margin']),
                'margin_free' => floatval($row['margin_free']),
                'margin_level' => floatval($row['margin_level']),
            ];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw new Exception($e->getMessage());
            }

            return false;
        }
    } 

    public static function getEquity(int $login): float|bool {
        try {
            $db = Database::connect();
            $sqlQuery = $db->query("SELECT IFNULL(EQUITY, 0) as EQUITY FROM mt4_users WHERE `LOGIN` = '{$login}' LIMIT 1");
            if($sqlQuery->num_rows != 1) {
                return false;
            }
            $row = $sqlQuery->fetch_assoc();
            return floatval($row['EQUITY']);
            
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw new Exception($e->getMessage());
            }

            return false;
        }
    }

}
