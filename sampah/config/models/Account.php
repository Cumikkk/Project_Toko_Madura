<?php
namespace App\Models;

use App\Library\Sales\SalesData;
use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Account {

    public static int $accountTypeDemo = 2;
    public static int $accountTypeReal = 1;
    public static array $allowedRights = [259, 355, 2307, 2311, 2401, 2403];

    public function __construct() {
        
    }

    public static function realAccountDetail(string $idAcc) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*,
                    tm.*,
                    (
                        SELECT
                            JSON_ARRAYAGG(
                                JSON_OBJECT(
                                    'ID_MBANK', tb_member_bank.ID_MBANK,
                                    'MBANK_NAME', tb_member_bank.MBANK_NAME,
                                    'MBANK_HOLDER', tb_member_bank.MBANK_HOLDER,
                                    'MBANK_ACCOUNT', tb_member_bank.MBANK_ACCOUNT
                                )
                            )
                        FROM tb_member_bank
                        WHERE tb_member_bank.MBANK_MBR = tm.MBR_ID
                        LIMIT 1
                    ) AS MBR_BKJSN,
                    (
                        SELECT
                            tb_note.NOTE_NOTE
                        FROM tb_note
                        WHERE tb_note.NOTE_RACC = tr.ID_ACC
                        AND tb_note.NOTE_TYPE IN('WP VER REJECT', 'BANK VERIFICATION REJECT')
                        ORDER BY tb_note.ID_NOTE DESC
                        LIMIT 1
                    ) AS RJCT_NOTE
                FROM tb_racc tr 
                JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE)
                WHERE UPPER(tra.RTYPE_TYPE) != 'DEMO'
                AND MD5(MD5(tr.ID_ACC)) = '{$idAcc}'
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function realAccountDetail_byLogin(string $login) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*,
                    tm.*
                FROM tb_racc tr 
                JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE)
                WHERE UPPER(tra.RTYPE_TYPE) != 'DEMO'
                AND tr.ACC_LOGIN = '{$login}'
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function accoundCondition(int $idAcc) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tac.*,
                    tm.MBR_NAME,
	                tm.MBR_EMAIL
                FROM `tb_acccond` tac
                JOIN tb_racc tr ON (tr.ID_ACC = tac.ACCCND_ACC)
                LEFT JOIN tb_member tm ON (tm.MBR_ID = tac.ACCCND_IB)
                WHERE tr.ID_ACC = {$idAcc}
                ORDER BY ID_ACCCND DESC
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function marginBalance(int $accLogin): float|bool {
        try {
            global $db;
            $sqlGetAccount = $db->query("SELECT MARGIN_FREE FROM mt4_users WHERE LOGIN = {$accLogin} LIMIT 1");
            if($sqlGetAccount->num_rows != 1) {
                return false;
            }

            $assoc = $sqlGetAccount->fetch_assoc();
            return floatval($assoc['MARGIN_FREE'] ?? 0) ;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function creditBalance(int $accLogin) {
        try {
            global $db;
            $sqlGetAccount = $db->query("SELECT CREDIT FROM MT4_USERS WHERE `LOGIN` = {$accLogin} LIMIT 1");
            if($sqlGetAccount->num_rows != 1) {
                return "Invalid Account";
            }

            $assoc = $sqlGetAccount->fetch_assoc();
            return floatval($assoc['CREDIT'] ?? 0) ;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                return $e->getMessage();
            }

            return "Invalid";
        }
    }

    public static function checkMetaDpwd(string $code) {
        try {
            global $db;
            $sqlGet = $db->query("SELECT TICKET FROM MT4_TRADES WHERE COMMENT = '{$code}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return [];
            }

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function accountConvertation(array $data = []): array|string {
        try {
            /** Required parameter */
            foreach(['account_id', 'amount', 'from', 'to'] as $req) {
                if(empty($data[ $req ])) {
                    return "{$req} is required";
                }
            }
    
            $from   = strtoupper($data['from'] ?? "");
            $to     = strtoupper($data['to'] ?? "");
            $amount = floatval($data['amount']);
            $rate   = 0;
    
            if(empty($from) || empty($to)) {
                return "Invalid From & To Currency";
            }
    
            if($amount <= 0) {
                return "Invalid Amount";
            }

            if($from == $to) {
                return [
                    'amount' => $amount,
                    'rate' => 1
                ];
            }
    
            /** Get Real Account */
            $realAccount = self::realAccountDetail(md5(md5($data['account_id'] ?? 0)));
            if(empty($realAccount)) {
                return "Invalid Account";
            }
    
            switch($realAccount['RTYPE_ISFLOATING']) {
                case 1:
                    $rate = Rate::autoCheckRate($from, $to);
                    if(!$rate) {
                        return "Rate has not been configured";
                    }
                    break;
    
                case 0: 
                    /** Jika bukan akun floating */
                    $rate = $realAccount['RTYPE_RATE'];
                    break;
            }
    
            /** Check Rate */
            if(is_numeric($rate) === FALSE || $rate <= 0) {
                return "Failed to get floating rate";
            }
           
            return [
                'amount' => $amount,
                'rate' => $rate
            ];

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getDemoAccount(string $userid, string $typeAs = "SPA"): array {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*,
                    IFNULL(mtud.BALANCE, 10000) as BALANCE,
                    mtud.CREDIT,
                    mtud.EQUITY,
                    mtud.MARGIN,    
                    mtud.MARGIN_FREE as FREE_MARGIN,
                    mtud.MARGIN_FREE as MARGIN_FREE,
                    mtud.LEVERAGE,
                    mtud.group AS meta_group
                FROM tb_racc tr 
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE) 
                LEFT JOIN mt4_users mtud ON (mtud.LOGIN = tr.ACC_LOGIN)
                WHERE UPPER(tra.RTYPE_TYPE) = 'DEMO'
                AND MD5(MD5(tr.ACC_MBR)) = '{$userid}' 
                AND UPPER(tra.RTYPE_TYPE_AS) = '{$typeAs}'
                AND tr.ACC_STS = -1
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getProgressRealAccount(string $userid): array  {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*
                FROM tb_racc tr 
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE) 
                WHERE UPPER(tra.RTYPE_TYPE) != 'DEMO'
                AND tr.ACC_STS IN (0, 1, 2)
                AND MD5(MD5(tr.ACC_MBR)) = '{$userid}'
                ORDER BY ID_ACC DESC 
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getProgressRealAccount_byID(string $idACc): array  {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*,
                    tm.*,
                    tacc.*,
                    tib.MBR_NAME as IB_NAME,
                    tib.MBR_EMAIL as IB_EMAIL
                FROM tb_racc tr 
                JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE)
                LEFT JOIN tb_acccond tacc ON (tacc.ACCCND_ACC = tr.ID_ACC AND tacc.ACCCND_STS != 1)
                LEFT JOIN tb_member tib ON (tib.MBR_ID = tacc.ACCCND_IB)
                WHERE UPPER(tra.RTYPE_TYPE) != 'DEMO'
                AND MD5(MD5(tr.ID_ACC)) = '{$idACc}'
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getLastAccount(string $userid) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*
                FROM tb_racc tr 
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE) 
                WHERE UPPER(tra.RTYPE_TYPE) != 'DEMO'
                AND tr.ACC_LOGIN != 0
                AND tr.ACC_WPCHECK = 6
                AND MD5(MD5(tr.ACC_MBR)) = '{$userid}'
                ORDER BY ID_ACC DESC 
                LIMIT 1
            ");

            if($sqlGet->num_rows == 0) {
                return [];
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            return [];
        }
    }

    public static function duplicateLastAccount(string $userid) {
        try {
            global $db;
            /** Get Last Account */
            $lastAccount = self::getLastAccount($userid);
            unset(
                $lastAccount['ID_ACC'], 
                $lastAccount['ACC_WPCHECK_DATE'], 
                $lastAccount['ACC_PASS'], 
                $lastAccount['ACC_INVESTOR'], 
                $lastAccount['ACC_TOKEN'], 
                $lastAccount['ACC_DATETIME'], 
                $lastAccount['ACC_PASSPHONE'], 
                $lastAccount['ACC_INITIALMARGIN'],
                $lastAccount['ACC_LAST_STEP'],
                $lastAccount['ACC_F_CMPLT'],
                $lastAccount['ACC_F_CMPLT_IP'],
                $lastAccount['ACC_F_CMPLT_PERYT'],
                $lastAccount['ACC_F_CMPLT_DATE'],
                $lastAccount['ACC_KODE_NASABAH'],
            );

            $datetime = date("Y-m-d H:i:s");
            $lastAccount['ACC_DATETIME'] = $datetime;
            $lastAccount['ACC_F_PROFILE_DATE'] = $datetime;
            $lastAccount['ACC_F_SIMULASI_DATE'] = $datetime;
            $lastAccount['ACC_F_PENGLAMAN_DATE'] = $datetime;
            $lastAccount['ACC_F_APPPEMBUKAAN_DATE'] = $datetime;
            $lastAccount['ACC_F_APP_DATE'] = $datetime;
            $lastAccount['ACC_F_RESK_DATE'] = $datetime;
            $lastAccount['ACC_F_PERJ_DATE'] = $datetime;
            $lastAccount['ACC_F_TRDNGRULE_DATE'] = $datetime;
            $lastAccount['ACC_F_KODE_DATE'] = $datetime;
            $lastAccount['ACC_F_DANA_DATE'] = $datetime;
            $lastAccount['ACC_F_DISC_DATE'] = $datetime;
            $lastAccount['ACC_F_DISC_DATE2'] = $datetime;
            $lastAccount['ACC_F_DISC_DATE3'] = $datetime;
            $lastAccount['ACC_F_DISC_DATE4'] = $datetime;
            $lastAccount['ACC_F_CMPLT_DATE'] = $datetime;
            $lastAccount['ACC_WPCHECK_DATE'] = $datetime;
            $lastAccount['ACC_STS'] = 0; 
            $lastAccount['ACC_LOGIN'] = 0;
            $lastAccount['ACC_WPCHECK'] = 0;

            /** Sql Insert New Record */
            $insert = Database::insert("tb_racc", $lastAccount);
            return $insert;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function allowToApplyReferral(string $userid): bool  {
        try {
            global $db;
            /**
             * Syarat
             * 1. Belum memiliki Upline / Upline Bukan Admin
             * 2. Belum create real account maupun progress real account
             */

            $sqlGet = $db->query("
                SELECT 
                    tm.MBR_IDSPN,
                    COUNT(tr.ID_ACC) as TOTAL_ACC
                FROM tb_member tm
                LEFT JOIN (
                    SELECT
                        ID_ACC,
                        ACC_MBR,
                        ACC_LOGIN
                    FROM tb_racc
                    JOIN tb_racctype ON (ID_RTYPE = ACC_TYPE)
                    WHERE UPPER(RTYPE_TYPE) != 'DEMO'
                ) as tr ON (tr.ACC_MBR = tm.MBR_ID)
                WHERE MD5(MD5(MBR_ID)) = '{$userid}'
                GROUP BY tm.MBR_ID
                LIMIT 1
            ");

            if($sqlGet->num_rows != 1) {
                return false;
            }

            $detail = $sqlGet->fetch_assoc();
            if($detail['TOTAL_ACC'] != 0) {
                return false;
            }

            return (empty($detail['MBR_IDSPN']) || $detail['MBR_IDSPN'] == 1000000000)
                ? true
                : false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function getAvailableProduct(string $userid, string $type = ""): array {
        try {
            global $db;
            $userData = User::findByMemberIdHash($userid);
            if(!$userData) {
                return [];
            }

            $accountCategory = "0";
            $salesData = SalesMain::getUserType($userData['MBR_TYPE']);
            if($salesData) {
                $division = $salesData->division();
                if($division) {
                    $accountCategory = $division['SLSDIVISION_RTYPECAT'];
                }
            }

            else {
                $getUpline = Ib::userUpline($userData['MBR_IDSPN']);
                if(!$getUpline || $getUpline['MBR_ID'] == 1000000000) {
                    $accountCategory = "2";
                    
                }else {
                    $uplineSales = SalesMain::getUserType($getUpline['MBR_TYPE']);
                    if(!$uplineSales) {
                        return [];
                    }
    
                    $uplineDivision = $uplineSales->division();
                    if(!$uplineDivision) {
                        return [];
                    }
                    
                    $accountCategory = $uplineDivision['SLSDIVISION_RTYPECAT'];
                }
            }

            $sqlGet = $db->query("
                SELECT 
                    tm.MBR_ID,
                    tm.MBR_NAME,
                    tra.*
                FROM tb_member tm
                JOIN (
                    SELECT 
                        ID_RTYPE,
                        RTYPE_CAT,
                        RTYPE_SUFFIX,
                        RTYPE_NAME,
                        RTYPE_TYPE_AS,
                        RTYPE_TYPE,
                        RTYPE_RATE,
                        RTYPE_CURR,
                        RTYPE_SPREAD,
                        RTYPE_MINTRADE,
                        RTYPE_MINDEPOSIT,
                        RTYPE_SWAP,
                        RTYPE_LEVERAGE,
                        RTYPE_STS,
                        RTYPE_KOMISI,
                        RTYPE_ISFLOATING,
                        RTYPE_SORT
                    FROM tb_racctype
                    WHERE UPPER(RTYPE_TYPE) != 'DEMO'
                    AND RTYPE_STS = -1
                ) as tra ON ((FIND_IN_SET(tra.RTYPE_SUFFIX, tm.MBR_SUFFIX) > 0 OR (tm.MBR_SUFFIX IS NULL AND tra.RTYPE_CAT IN ({$accountCategory}))) AND tra.RTYPE_STS = -1)
                WHERE MD5(MD5(MBR_ID)) = '{$userid}'
                AND (tm.MBR_SUFFIX_EXCLUDE IS NULL OR tm.MBR_SUFFIX_EXCLUDE NOT LIKE CONCAT('%', tra.RTYPE_SUFFIX, '%'))
                GROUP BY tra.ID_RTYPE
                ORDER BY tra.RTYPE_SORT
            ");

            if($sqlGet->num_rows == 0) {
                return [];
            }

            $products = [];
            foreach($sqlGet->fetch_all(MYSQLI_ASSOC) as $product) {
                if(!empty($type)) {
                    if(strtoupper($product['RTYPE_TYPE_AS'] ?? "") != strtoupper($type)) {
                        continue;
                    }
                }

                $productType = strtolower($product['RTYPE_TYPE'] ?? "");
                $index = array_search($productType, array_column($products, "type"));
                if($index === FALSE) {
                    $products[] = [
                        'type' => $productType
                    ];

                    $index = array_search($productType, array_column($products, "type"));
                }

                $products[ $index ]['products'][] = $product;
            }

            return $products;
           

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getAvailableProduct_list(string $userid): array {
        try {
            global $db;
            $availableAccounts = self::getAvailableProduct($userid);
            $result = [];
            foreach($availableAccounts as $available) {
                foreach($available['products'] as $product) {
                    $result[] = $product;
                }
            }
            
            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function checkAccountSuffix(string $suffix): array {
        try {
            global $db;
            $sqlGet = $db->query("SELECT * FROM tb_racctype WHERE RTYPE_SUFFIX = '{$suffix}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return [];
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getDepositNewAccount_data(int $idAcc) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    td.*
                FROM tb_dpwd td
                JOIN tb_racc tr ON (tr.ID_ACC = td.DPWD_RACC)
                WHERE tr.ID_ACC = {$idAcc}
                AND td.DPWD_TYPE = 3
                AND td.DPWD_STS != 1
                ORDER BY td.ID_DPWD DESC
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getDepositNewAccount_History(int $idAcc) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    td.*,
                    tn.*
                FROM tb_dpwd td
                JOIN tb_racc tr ON (tr.ID_ACC = td.DPWD_RACC)
                JOIN tb_note tn ON (tn.NOTE_RACC = tr.ID_ACC AND tn.NOTE_DPWD = td.ID_DPWD)
                WHERE tr.ID_ACC = {$idAcc}
                AND td.DPWD_TYPE = 3
                ORDER BY td.ID_DPWD DESC
            ");

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function havePendingTransaction(int $mbrid, array $dpwd_type = [1]) {
        try {
            global $db;
            $type = implode(",", $dpwd_type);
            $sqlGet = $db->query("SELECT ID_DPWD FROM tb_dpwd WHERE DPWD_MBR = {$mbrid} AND DPWD_TYPE IN ({$type}) AND DPWD_STS = 0");
            return ($sqlGet->num_rows != 0);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function accountCommission() {
        try {
            global $db;
            $sqlGet = $db->query("SELECT RTYPE_KOMISI FROM tb_racctype WHERE UPPER(RTYPE_TYPE) != 'DEMO' AND RTYPE_KOMISI > 0 GROUP BY RTYPE_KOMISI ORDER BY RTYPE_KOMISI");
            if($sqlGet->num_rows == 0) {
                return [];
            }

            return array_map(fn($ar): int => $ar['RTYPE_KOMISI'], $sqlGet->fetch_all(MYSQLI_ASSOC));

        } catch (Exception $e) {
            return [];
        }
    }

    public static function generatePassword(int $len = 5): string {
        $lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $specials = array('!','#','$','&','(',')','*','+',',','-','.',':',';','=','?','@','[',']','_',);
        $digits = array('0','1','2','3','4','5','6','7','8','9');
        $all = array($lower, $upper, $specials, $digits);

        $pwd = $lower[array_rand($lower, 1)];
        $pwd = $pwd . $upper[array_rand($upper, 1)];
        $pwd = $pwd . $specials[array_rand($specials, 1)];
        $pwd = $pwd . $digits[array_rand($digits, 1)];

        for($i = strlen($pwd); $i < max(8, $len); $i++)
        {
            $temp = $all[array_rand($all, 1)];
            $pwd = $pwd . $temp[array_rand($temp, 1)];
        }

        return str_shuffle($pwd);
    }

    public static function all(int $mbrid) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racc JOIN tb_racctype ON (ID_RTYPE = ACC_TYPE) WHERE ACC_MBR = $mbrid AND ACC_DERE = 1 AND ACC_LOGIN != '0' AND ACC_STS = -1");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
        // $apiMeta        = ApiMetatrader();
        // $tokenManager   = $apiMeta->token_manager_demo;
    }

    public static function myAccount(int $mbrid) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    trt.*,
                    mt4u.RIGHTS as rights,
                    mt4u.MARGIN_FREE,
                    mt4u.EQUITY,
                    mt4u.GROUP as meta_group
                FROM tb_racc tr
                JOIN tb_racctype trt ON (trt.ID_RTYPE = tr.ACC_TYPE) 
                LEFT JOIN mt4_users mt4u ON (mt4u.login = tr.ACC_LOGIN)
                WHERE tr.ACC_MBR = $mbrid 
                AND tr.ACC_DERE = 1 
                AND tr.ACC_STS = -1
                GROUP BY tr.ID_ACC
            ");

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function findByCode(string $code): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    * 
                FROM tb_racc 
                JOIN tb_member tm ON (tm.MBR_ID = ACC_MBR)
                JOIN tb_racctype trt ON (trt.ID_RTYPE = ACC_TYPE) 
                WHERE ACC_KODE = '{$code}' 
                AND ACC_KODE IS NOT NULL
            ");

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

    public static function accountExclude(int $mbrid): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT MBR_SUFFIX_EXCLUDE FROM tb_member WHERE MBR_ID = {$mbrid} LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return [];
            }

            return explode(",", ($sqlGet->fetch_assoc()['MBR_SUFFIX_EXCLUDE'] ?? ""));

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function accountInclude(int $mbrid): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT MBR_SUFFIX FROM tb_member WHERE MBR_ID = {$mbrid} LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return [];
            }

            return explode(",", ($sqlGet->fetch_assoc()['MBR_SUFFIX'] ?? ""));

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function myFirstAccount(int $mbrid): array|bool {
        try {
            $myAccounts = self::myAccount($mbrid);
            if(count($myAccounts) <= 1) {
                return false;
            }

            return $myAccounts[0];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findByLogin(?string $login): array|bool {
        try {
            $login ??= "--";
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    * 
                FROM tb_racc 
                JOIN tb_member tm ON (tm.MBR_ID = ACC_MBR)
                JOIN tb_racctype trt ON (trt.ID_RTYPE = ACC_TYPE) 
                WHERE ACC_LOGIN = '{$login}' 
            ");

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

    public static function check_account_id(string $id) {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*,
                    tm.*
                FROM tb_racc tr 
                JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE)
                WHERE MD5(MD5(tr.ID_ACC)) = '{$id}'
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function isHaveCddsAccount(int $mbrid): bool {
        try {
            global $db;
            $sqlGet = $db->query("SELECT ID_ACC FROM tb_racc WHERE ACC_DERE = 1 AND ACC_CDD = " . Regol::$cddTypeSederhana . " AND ACC_MBR = {$mbrid} AND ACC_STS = -1 LIMIT 1");
            return ($sqlGet->num_rows != 0);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function isHaveCddAccount(int $mbrid): bool {
        try {
            global $db;
            $sqlGet = $db->query("SELECT ID_ACC FROM tb_racc WHERE ACC_DERE = 1 AND ACC_CDD = " . Regol::$cddTypeStandard . " AND ACC_MBR = {$mbrid} AND ACC_STS = -1 LIMIT 1");
            return ($sqlGet->num_rows != 0);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function calcResidualMargin($equity, $limitMarginUSD): float {
        $limit = $limitMarginUSD - $equity;
        return $limit > 0 ? $limit : 0;
    }

}