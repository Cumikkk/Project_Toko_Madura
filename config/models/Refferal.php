<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Refferal {

    public static $endpoint = "";

    public static $typeUserRefferal = "user";
    public static $typeGroupRefferal = "group";
    public static $typeAccountRefferal = "account";

    public function __construct() {
        $this->endpoint = SystemInfo::app('CLIENT_URL') . "/referral";
    }

    public static function parseProductType(string $type): string {
        return strtolower(str_replace("-", "", $type));
    } 

    public static function createUserReferral(int $mbrid): string|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_ID = {$mbrid} LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            $userData = $sqlGet->fetch_assoc();
            return SystemInfo::app('CLIENT_URL')."/signup?referral=".($userData['MBR_CODE'] ?? "-");

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function createUserGroupReferral(int $mbrid): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    tm.MBR_ID,
                    tm.MBR_EMAIL,
                    tm.MBR_NAME,
                    tr.ID_ACC,
                    tr.ACC_MBR,
                    tr.ACC_LOGIN,
                    tr.ACC_KODE,
                    trt.RTYPE_NAME,
                    trt.RTYPE_TYPE,
                    trt.RTYPE_RATE,
                    trt.RTYPE_KOMISI
                FROM tb_member tm
                JOIN tb_racc tr ON (tr.ACC_MBR = tm.MBR_ID)
                JOIN tb_racctype trt ON (trt.ID_RTYPE = ACC_TYPE)
                WHERE MBR_ID = {$mbrid}
                AND tr.ACC_DERE = 1
                AND tr.ACC_WPCHECK = 6
                AND tr.ACC_STS = -1
                GROUP BY trt.RTYPE_TYPE
            ");

            /** Get Account */
            $result = [];
            $accounts = $sqlGet->fetch_all(MYSQLI_ASSOC);
            if(empty($accounts)) {
                return [];
            }

            foreach($accounts as $acc) {
                $result[] = [
                    'type' => $acc['RTYPE_TYPE'],
                    'link' => self::$endpoint . "/" . implode("-", [self::parseProductType($acc['RTYPE_TYPE']), md5($acc['ACC_MBR'] . $acc['RTYPE_TYPE'])])
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

    public static function createAccountReferral(int $mbrid): array {
        try {
            $result = [];
            $accounts = Account::myAccount($mbrid);
            foreach($accounts as $acc) {
                $result[] = [
                    'name' => $acc['RTYPE_NAME'],
                    'type' => $acc['RTYPE_TYPE'],
                    'login' => $acc['ACC_LOGIN'],
                    'rate' => ($acc['RTYPE_ISFLOATING'])? "Floating" : ($acc['RTYPE_RATE'] / 1000),
                    'commission' => $acc['RTYPE_KOMISI'], 
                    'link' => self::$endpoint . "/" . implode("-", [$acc['RTYPE_SUFFIX'], self::parseProductType($acc['RTYPE_TYPE']), $acc['ACC_KODE']])
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

    public static function createAccountReferralV2(int $mbrid): array {
        try {
            $result = [];
            $userdata = User::findByMemberId($mbrid);
            if(!$userdata) {
                return [];
            }
            
            $availableAccounts = Account::getAvailableProduct(md5(md5($mbrid)));
            foreach($availableAccounts as $type) {
                foreach($type['products'] as $product) {
                    $result[] = [
                        'name' => $product['RTYPE_NAME'],
                        'type' => $type['type'],
                        'rate' => ($product['RTYPE_ISFLOATING'])? "Floating" : ($product['RTYPE_RATE'] / 1000),
                        'commission' => $product['RTYPE_KOMISI'], 
                        'link' => SystemInfo::app('CLIENT_URL') . "/signup?referral=" . implode("-", [$product['RTYPE_SUFFIX'], self::parseProductType($product['RTYPE_TYPE']), $userdata['MBR_CODE']])
                    ];
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

    public static function findGroupRefferal(string $groupRefferal) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    trt.*,
                    tm.*
                FROM tb_racc tr
                JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
                JOIN tb_racctype trt ON (trt.ID_RTYPE = tr.ACC_TYPE) 
                WHERE MD5(CONCAT(ACC_MBR, RTYPE_TYPE)) = '{$groupRefferal}'
                LIMIT 1
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
}