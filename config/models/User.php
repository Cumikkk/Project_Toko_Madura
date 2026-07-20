<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Config\Core\UserAuth;
use Exception;

class User extends UserAuth {

    public static $identityType = ['KTP'];
    public static int $statusActive = -1; 
    public static int $statusRegistered = 0; 
    public static int $statusDisabled = 1; 
    public static int $statusUnverified = 2; 
    public static int $statusLocked = 3; 

    public static function createMbrId(): int {
        try {
            global $db;
            if(empty($db)) {
                $db = Database::connect();
            }

            $select = $db->query("SELECT UNIX_TIMESTAMP(NOW())+(SELECT IFNULL(MAX(tb.ID_MBR),0) FROM tb_member tb) as ID");
            return $select->fetch_assoc()['ID'] ?? 0;

        } catch (Exception $e) {
            return 0;
        }
    }
    
    public static function user(): bool|array {
        try {
            /** Return array on success and bool on error */
            $userid = self::authentication();
            if(!$userid) {
                return false;
            }
    
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_ID = {$userid} LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            $user = $sqlGet->fetch_assoc();
            $user['userid'] = md5(md5($userid));
            
            if($user['MBR_LOCKED']) {
                return false;
            }
            
            return $user;

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return false;
        }
    } 

     public static function avatar(string $filename): string {
        if(empty($filename) || $filename == "-") {
            return "/assets/images/admin.png";
        }

        return FileUpload::awsFile($filename);
    } 

    public static function validation_password($input, int $min_length = 8): bool|string {
        $character  = "abcdefghijklmnopqrstuvwxyz";
        $numeric    = "1234567890";
        $return     = [
            'upper'     => 0,
            'lower'     => 0,
            'numeric'   => 0
        ];

        // Validate Length
        if(strlen($input) < $min_length) {
            return  "Password must be at least {$min_length} characters";
        }

        // Validate Character
        foreach(str_split($character) as $char) {
            //Uppercase 
            if($return['upper'] == 0) {
                if(strpos($input, strtoupper($char)) !== FALSE) {
                    $return['upper'] += 1;
                } 
            }

            //Lowercase
            if($return['lower'] == 0) {
                if(strpos($input, strtolower($char)) !== FALSE) {
                    $return['lower'] += 1;
                }
            }
        }

        // Validate Numeric
        foreach(str_split($numeric) as $num) {
            if($return['numeric'] == 0) {
                if(strpos($input, $num) !== FALSE) {
                    $return['numeric'] += 1;
                }
            }
        }

        if($return['upper'] == 0) {
            return  "Password must contain at least one upper case letter.";
        }

        if($return['lower'] == 0) {
            return  "Password must contain at least one lower case letter.";
        }

        if($return['numeric'] == 0) {
            return  "Password must contain at least one number.";
        }

        if(preg_match('/[^a-zA-Z0-9]/', $input) <= 0) {
            return  "Password must contain symbols.";
        }

        return true;
    }

    public static function allowToApplyReferral(string $userid) {
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

    public static function isVerified(int $mbrid) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member_file WHERE MBRFILE_MBR = {$mbrid}");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            $detail = $sqlGet->fetch_assoc() ?? false;
            return $detail;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function setResetPasswordCode(int $mbrid, string $code) {
        try {
            $expired = date("Y-m-d H:i:s", strtotime("+10 minute"));
            $update = Database::update("tb_member", ['MBR_RESET_CODE' => $code, 'MBR_RESET_EXPIRED' => $expired], ['MBR_ID' => $mbrid]);
            if(!$update) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function verifyResetCode(string $code) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT MBR_ID, MBR_EMAIL, MBR_RESET_EXPIRED FROM tb_member WHERE MBR_RESET_CODE = '{$code}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            $userData = $sqlGet->fetch_assoc();
            if(time() >= strtotime($userData['MBR_RESET_EXPIRED'] ?? "now")) {
                return false;
            }

            return $userData['MBR_ID'];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function myBank(int $mbrid, string $md5_id_bank = "-") {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member_bank WHERE MBANK_MBR = $mbrid");

            $result = [];
            switch(true) {
                case ($md5_id_bank == "-"):
                    $result = $sqlGet->fetch_all(MYSQLI_ASSOC);
                    break;


                case ($md5_id_bank != "-"):
                    $arr = $sqlGet->fetch_all(MYSQLI_ASSOC);
                    foreach($arr as $a) {
                        if(md5(md5($a['ID_MBANK'])) == $md5_id_bank) {
                            $result = $a;
                        }
                    }
                    break;

            }
            
            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function get_ib_data(int $mbrid = 0, array $status = [0, -1, 1]): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    tbi.*,
                    tm.MBR_NAME,
                    tm.MBR_EMAIL,
                    tm.MBR_ID,
                    tm.MBR_TYPE
                FROM tb_become_ib tbi
                JOIN tb_member tm ON (tm.MBR_ID = tbi.BECOME_MBR)
                WHERE BECOME_MBR = {$mbrid} 
                AND BECOME_STS IN (".implode(",", $status).")
                ORDER BY ID_BECOME DESC
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? false;       

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findByMemberId(int $mbrid = 0): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_ID = {$mbrid} LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;       

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findByMemberIdHash(string $userid = ""): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MD5(MD5(MBR_ID)) = '{$userid}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;       

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function checkReqDeleteAccount(?int $userid = null): bool|array {
        try {
            /** Return array on success and bool on error */
            if(!$userid) {
                $userid = self::authentication();
            }

            /** check user id ulang */
            if(!$userid) {
                return false;
            }
    
            $db = Database::connect();
            $sqlGet = $db->query("SELECT 1 FROM tb_dlt_account WHERE DLTACC_MBR = {$userid} AND DLTACC_STS = 0 ORDER BY ID_DLTACC LIMIT 1");
            return ($sqlGet->num_rows == 0) ? true : false;

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return false;
        }
    }

    public static function findByCode(?string $code = ""): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_CODE = '{$code}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;       

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findUplineByMembeId(int $mbrid): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_ID = {$mbrid} LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;       

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function nmiSetting(?int $mbrid = 0): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT NMI_TARGET, NMI_PERCENT FROM tb_nmi WHERE NMI_MBR = {$mbrid} LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            $row = $sqlGet->fetch_assoc();
            return [
                'target' => $row['NMI_TARGET'],
                'percent' => $row['NMI_PERCENT'],
            ];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function wallet(int $mbrid, array $type = []): float {
        try {
            $db = Database::connect();
            if(empty($type)) {
                $type = [0];
            }

            $typeIn = implode(",", [Dpwd::$typeRebateCommission]);
            $typeOut = implode(",", [Dpwd::$typeWithdrawalCommission]);
           
            $totalIn = 0;
            $totalOut = 0;

            $sqlGetIn = $db->query("SELECT SUM(DPWD_AMOUNT) as TOTAL_IN FROM tb_dpwd WHERE DPWD_TYPE IN ({$typeIn}) AND DPWD_MBR = {$mbrid} AND DPWD_STS = -1 LIMIT 1");
            if($sqlGetIn && $sqlGetIn->num_rows == 1) {
                $totalIn = $sqlGetIn->fetch_assoc()['TOTAL_IN'] ?? 0;
            }

            $sqlGetOut = $db->query("SELECT SUM(DPWD_AMOUNT_SOURCE) as TOTAL_OUT FROM tb_dpwd WHERE DPWD_TYPE IN ({$typeOut}) AND DPWD_MBR = {$mbrid} AND DPWD_STS != 1 LIMIT 1");
            if($sqlGetOut && $sqlGetOut->num_rows == 1) {
                $totalOut = $sqlGetOut->fetch_assoc()['TOTAL_OUT'] ?? 0;
            }

            $total = $totalIn - $totalOut;
            return ($total < 0)? 0 : $total;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return 0;
        }
    }

    public static function findByEmail(string $email = ""): array|bool {
        try {
            if(empty($email)) {
                return false;
            }
            $db = Database::connect();
            $email = $db->real_escape_string($email);
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_EMAIL = '{$email}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;       

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function status(int $status) {
        switch($status) {
            case self::$statusActive: 
                return [
                    'text' => "Active",
                    'html' => '<span class="badge bg-success">Active</span>'
                ];

            case self::$statusRegistered: 
                return [
                    'text' => "Pending",
                    'html' => '<span class="badge bg-warning">Pending</span>'
                ];

            case self::$statusDisabled: 
                return [
                    'text' => "Disabled",
                    'html' => '<span class="badge bg-danger">Disabled</span>'
                ];

            case self::$statusUnverified: 
                return [
                    'text' => "Unverified",
                    'html' => '<span class="badge bg-primary">Unverified</span>'
                ];

            case self::$statusLocked: 
                return [
                    'text' => "Locked",
                    'html' => '<span class="badge bg-danger">Locked</span>'
                ];

            default: 
                return [
                    'text' => "Unknown",
                    'html' => '<span class="badge bg-dark">Unknown</span>'
                ];
        }
    } 
}