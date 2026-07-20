<?php
namespace App\Library\MT5;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;
use Google\Protobuf\FloatValue;

class Mt5Trades {

    public static function getTradingAccounts_FilterByDateGroupByMemberID(?string $dateStart, ?string $dateEnd, ?int $mbrid = null): array {
        try {
            /** Get Trading Accounts (buy, sell) based on datestart & dateend, and mbrid if necessary */
            $db = Database::connect();
            $dateStart ??= "";
            $dateEnd ??= "";
            $mbrid ??= 0;
            $result = [];

            if(!empty($groupBy)) {
                $groupBy = "GROUP BY " . implode(", ", $groupBy);
            }

            $sqlGetAccountTrading = $db->query("
                SELECT
                    ta.ACC_MBR,
                    tm.MBR_EMAIL,
                    tm.MBR_NAME,
                    tm.MBR_CODE,
                    mt5t.TICKETS,
                    GROUP_CONCAT(ta.ACC_LOGIN SEPARATOR ',') as LOGINS,
                    COALESCE(SUM(mt5t.TOTAL_LOT), 0) as TOTAL_LOT
                FROM tb_racc ta
                JOIN tb_member tm ON (tm.MBR_ID = ta.ACC_MBR)
                JOIN (
                    SELECT 
                        LOGIN,
                        GROUP_CONCAT(TICKET SEPARATOR ',') as TICKETS,
                        COALESCE(SUM(VOLUME), 0) as TOTAL_LOT
                    FROM mt5_trades
                    WHERE LOWER(CMD) IN ('buy', 'sell')
                    AND NOT EXISTS (SELECT H_TICKET FROM tb_rebate_history WHERE H_TICKET = TICKET)
                    AND DATE(CLOSE_TIME) BETWEEN '$dateStart' AND '$dateEnd'
                    GROUP BY LOGIN
                ) as mt5t ON (mt5t.LOGIN = ta.ACC_LOGIN)
                WHERE (ta.ACC_MBR = {$mbrid} OR {$mbrid} = 0)
                AND ta.ACC_DERE = 1
                GROUP BY ta.ACC_MBR
            ");

            foreach($sqlGetAccountTrading->fetch_all(MYSQLI_ASSOC) as $row) {
                $result[] = [
                    'mbrid' => $row['ACC_MBR'],
                    'name' => $row['MBR_NAME'],
                    'email' => $row['MBR_EMAIL'],
                    'code' => $row['MBR_CODE'],
                    'tickets' => $row['TICKETS'],
                    'logins' => $row['LOGINS'],
                    'lots' => floatval($row['TOTAL_LOT']),
                ];
            }

            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getTradingAccounts_FilterByDateGroupBySymbolCategory(?string $dateStart, ?string $dateEnd, ?int $mbrid = null): array {
        try {
            /** Get Trading Accounts (buy, sell) based on datestart & dateend, and mbrid if necessary */
            $db = Database::connect();
            $dateStart ??= "";
            $dateEnd ??= "";
            $mbrid ??= 0;
            $result = [];

            if(!empty($groupBy)) {
                $groupBy = "GROUP BY " . implode(", ", $groupBy);
            }

            $sqlGetAccountTrading = $db->query("
                SELECT
                    ta.ACC_MBR,
                    tm.MBR_EMAIL,
                    tm.MBR_NAME,
                    tm.MBR_CODE,
                    mt5t.TICKETS,
                    GROUP_CONCAT(ta.ACC_LOGIN SEPARATOR ',') as LOGINS,
                    mt5t.ID_SYMCAT,
                    mt5t.SYMCAT_NAME,
                    COALESCE(SUM(mt5t.TOTAL_LOT), 0) as TOTAL_LOT
                FROM tb_racc ta
                JOIN tb_member tm ON (tm.MBR_ID = ta.ACC_MBR)
                JOIN (
                    SELECT 
                        LOGIN,
                        GROUP_CONCAT(TICKET SEPARATOR ',') as TICKETS,
                        COALESCE(SUM(VOLUME), 0) as TOTAL_LOT,
                        tsc.ID_SYMCAT,
                        tsc.SYMCAT_NAME
                    FROM mt5_trades
                    JOIN tb_symbol tsy ON (tsy.SYM_NAME = SYMBOL)
                    JOIN tb_symbolcat tsc ON (tsc.ID_SYMCAT = tsy.ID_SYMCAT)
                    WHERE LOWER(CMD) IN ('buy', 'sell')
                    AND NOT EXISTS (SELECT H_TICKET FROM tb_rebate_history WHERE H_TICKET = TICKET)
                    AND DATE(CLOSE_TIME) BETWEEN '$dateStart' AND '$dateEnd'
                    GROUP BY LOGIN, tsy.ID_SYMCAT
                ) as mt5t ON (mt5t.LOGIN = ta.ACC_LOGIN)
                WHERE (ta.ACC_MBR = {$mbrid} OR {$mbrid} = 0)
                AND ta.ACC_DERE = 1
                GROUP BY ta.ACC_MBR, mt5t.ID_SYMCAT
            ");

            foreach($sqlGetAccountTrading->fetch_all(MYSQLI_ASSOC) as $row) {
                $result[] = [
                    'mbrid' => $row['ACC_MBR'],
                    'name' => $row['MBR_NAME'],
                    'email' => $row['MBR_EMAIL'],
                    'code' => $row['MBR_CODE'],
                    'tickets' => $row['TICKETS'],
                    'symbol' => [
                        'id' => $row['ID_SYMCAT'],
                        'name' => $row['SYMCAT_NAME']
                    ],
                    'logins' => $row['LOGINS'],
                    'lots' => floatval($row['TOTAL_LOT']),
                ];
            }

            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getTradingAccounts_FilterByDateGroupByProduct(?string $dateStart, ?string $dateEnd, ?int $mbrid = null): array {
        try {
            /** Get Trading Accounts (buy, sell) based on datestart & dateend, and mbrid if necessary */
            $db = Database::connect();
            $dateStart ??= "";
            $dateEnd ??= "";
            $mbrid ??= 0;
            $result = [];

            if(!empty($groupBy)) {
                $groupBy = "GROUP BY " . implode(", ", $groupBy);
            }

            $sqlGetAccountTrading = $db->query("
                SELECT
                    ta.ACC_MBR,
                    ta.ACC_TYPE,
                    tm.MBR_EMAIL,
                    tm.MBR_NAME,
                    tm.MBR_CODE,
                    mt5t.TICKETS,
                    GROUP_CONCAT(ta.ACC_LOGIN SEPARATOR ',') as LOGINS,
                    mt5t.ID_SYMCAT,
                    mt5t.SYMCAT_NAME,
                    COALESCE(SUM(mt5t.TOTAL_LOT), 0) as TOTAL_LOT
                FROM tb_racc ta
                JOIN tb_member tm ON (tm.MBR_ID = ta.ACC_MBR)
                JOIN (
                    SELECT 
                        LOGIN,
                        GROUP_CONCAT(TICKET SEPARATOR ',') as TICKETS,
                        COALESCE(SUM(VOLUME), 0) as TOTAL_LOT,
                        tsc.ID_SYMCAT,
                        tsc.SYMCAT_NAME
                    FROM mt5_trades
                    JOIN tb_symbol tsy ON (tsy.SYM_NAME = SYMBOL)
                    JOIN tb_symbolcat tsc ON (tsc.ID_SYMCAT = tsy.ID_SYMCAT)
                    WHERE LOWER(CMD) IN ('buy', 'sell')
                    AND NOT EXISTS (SELECT H_TICKET FROM tb_rebate_history WHERE H_TICKET = TICKET)
                    AND DATE(CLOSE_TIME) BETWEEN '$dateStart' AND '$dateEnd'
                    GROUP BY LOGIN, tsy.ID_SYMCAT
                ) as mt5t ON (mt5t.LOGIN = ta.ACC_LOGIN)
                WHERE (ta.ACC_MBR = {$mbrid} OR {$mbrid} = 0)
                AND ta.ACC_DERE = 1
                GROUP BY ta.ACC_MBR, mt5t.ID_SYMCAT, ta.ACC_TYPE
            ");

            foreach($sqlGetAccountTrading->fetch_all(MYSQLI_ASSOC) as $row) {
                $result[] = [
                    'mbrid' => $row['ACC_MBR'],
                    'name' => $row['MBR_NAME'],
                    'email' => $row['MBR_EMAIL'],
                    'code' => $row['MBR_CODE'],
                    'tickets' => $row['TICKETS'],
                    'symbol' => [
                        'id' => $row['ID_SYMCAT'],
                        'name' => $row['SYMCAT_NAME']
                    ],
                    'id_product' => $row['ACC_TYPE'],
                    'logins' => $row['LOGINS'],
                    'lots' => floatval($row['TOTAL_LOT']),
                ];
            }

            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

}