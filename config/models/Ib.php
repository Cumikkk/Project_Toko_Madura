<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Ib {

    public static array $userType = [
        '1' => "Mib",
        '2' => "Ib",
        '3' => "Trader"
    ];

    public static array $status = [
        '-1' => [
            'text' => "Success",
            'html' => '<span class="badge bg-success">Success</span>'
        ],
        '0' => [
            'text' => "Pending",
            'html' => '<span class="badge bg-warning">Pending</span>'
        ],
        '1' => [
            'text' => "Rejected",
            'html' => '<span class="badge bg-danger">Rejected</span>'
        ],
    ];

    public static array $requiredAccounts = ['STANDARD', 'STANDARD-PLUS'];

    public static function getIbType(): bool|int {
        return array_search("Ib", self::$userType);
    }

    public static function getTraderType(): bool|int {
        return array_search("Trader", self::$userType);
    }

    public static function isAllowToBecomeIb(string $mbridHash): array {
        try {
            /** 
             * Persyaratan
             * - Memiliki Standard Account
             * - Memiliki setidaknya $100 margin free di salah satu account (bukan summary)
             * 
            *  */
            $result = [
                'success' => false,
                'requirements' => [
                    'haveRequiredAccount' => [
                        'status' => false,
                        'text' => "Have a Trading Account",
                    ],
                    'haveEnoughBalance' => [
                        'status' => false,
                        'text' => "Have at least $100 free margin in real accounts"
                    ]
                ]
            ];

            $result['requirements']['haveRequiredAccount']['status'] = true;
            $result['requirements']['haveEnoughBalance']['status'] = true;

            $user = User::findByMemberIdHash($mbridHash);
            if(!$user) {
                return $result;
            }

            /** Check Upline level */
            $upline = User::findUplineByMembeId($user['MBR_IDSPN']);
            if(!$upline) {
                return $result;
            }

            if($upline['MBR_ID'] == 1000000000) {
                $result['success'] = true;
                return $result;
            }

            /** Check upline type */
            $structure = SalesStructure::findById($upline['MBR_TYPE']);
            if(!$structure) {
                return $result;
            }

            /** Check sales level */
            $db = Database::connect();
            $sqlGetStructure = $db->query("SELECT * FROM tb_sales_structure WHERE SLSSTRC_DIV = {$structure['SLSSTRC_DIV']} AND SLSSTRC_LEVEL = ".($structure['SLSSTRC_LEVEL'] + 1)." LIMIT 1");
            if($sqlGetStructure->num_rows <= 0) {
                return $result;
            }

            $result['success'] = true;
            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return $result;
        }
    }

    public static function haveRequiredAccount(string $userid): array|bool {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    ID_ACC, 
                    ACC_LOGIN, 
                    RTYPE_CURR, 
                    ACC_TYPE, 
                    RTYPE_TYPE,
                    ACC_PASS,
                    ACC_INVESTOR,
                    mt4u.BALANCE,
                    mt4u.MARGIN_FREE
                FROM tb_racc 
                JOIN tb_racctype ON (ID_RTYPE = ACC_TYPE)
                JOIN mt4_users mt4u ON (mt4u.LOGIN = ACC_LOGIN)
                WHERE MD5(MD5(ACC_MBR)) = '{$userid}'
                AND ACC_DERE = 1 
                AND ACC_LOGIN != 0
                AND ACC_WPCHECK = 6
                #AND FIND_IN_SET(UPPER(RTYPE_TYPE), '".implode(",", self::$requiredAccounts)."') 
            ");

            if($sqlGet->num_rows == 0) {
                return false;
            }

            return $sqlGet->fetch_all(MYSQLI_ASSOC);

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
            $sqlGet = $db->query("SELECT * FROM tb_become_ib WHERE MD5(MD5(ID_BECOME)) = '{$id}' LIMIT 1");
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

    public static function userUpline($mbrid): array|bool {
        try {
            $db = Database::connect();
            $mbrid ??= 1000000000;
            $sqlGet = $db->query("SELECT * FROM tb_member WHERE MBR_ID = {$mbrid} LIMIT 1");
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

    public static function getNetworks(int $mbrId, string $format = "downline"): array {
        global $db;
        $format = strtolower($format);
        if(!in_array($format, ["downline", "upline"])) {
            return [];
        }

        try {
            switch($format) {
                case "downline" :
                    $query = "
                        WITH RECURSIVE member_hierarchy AS (
                            SELECT 
                                MBR_ID,
                                MBR_NAME,
                                MBR_CODE,
                                MBR_IDSPN,
                                MBR_EMAIL,
                                MBR_TYPE,
                                MBR_PHONE,
                                MBR_STS
                            FROM tb_member
                            WHERE MBR_ID = {$mbrId}
                            UNION ALL
                            SELECT 
                                m.MBR_ID,
                                m.MBR_NAME,
                                m.MBR_CODE,
                                m.MBR_IDSPN,
                                m.MBR_EMAIL,
                                m.MBR_TYPE,
                                m.MBR_PHONE,
                                m.MBR_STS
                            FROM tb_member m
                            INNER JOIN member_hierarchy mh ON m.MBR_IDSPN = mh.MBR_ID
                        )

                        SELECT 
                            mh.*,
                            IFNULL(tss.SLSSTRC_NAME, 'Trader') as SALES_TYPE 
                        FROM member_hierarchy mh
                        LEFT JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = mh.MBR_TYPE)
                    ";
                    break;

                case "upline" :
                    $query = "
                        WITH RECURSIVE member_hierarchy AS (
                            SELECT 
                                MBR_ID,
                                MBR_NAME,
                                MBR_CODE,
                                MBR_IDSPN,
                                MBR_EMAIL,
                                MBR_TYPE,
                                MBR_PHONE,
                                MBR_STS
                            FROM tb_member
                            WHERE MBR_ID = {$mbrId}
                            UNION ALL
                            SELECT 
                                m.MBR_ID,
                                m.MBR_NAME,
                                m.MBR_CODE,
                                m.MBR_IDSPN,
                                m.MBR_EMAIL,
                                m.MBR_TYPE,
                                m.MBR_PHONE,
                                m.MBR_STS
                            FROM tb_member m
                            INNER JOIN member_hierarchy mh ON m.MBR_ID = mh.MBR_IDSPN
                        )
                            
                        SELECT 
                            mh.*,
                            IFNULL(tss.SLSSTRC_NAME, 'Trader') as SALES_TYPE 
                        FROM member_hierarchy mh
                        LEFT JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = mh.MBR_TYPE)
                    ";
                    break;
            }
            
            $sqlGetDownline = $db->query($query);
            $data = $sqlGetDownline->fetch_all(MYSQLI_ASSOC);
    
            return $data;
        
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function toHierarcy(array $array = []): array {
        try {
            $structures = [];
            foreach($array as &$usr) {
                $structures[ $usr['MBR_ID'] ] = $usr;
                $structures[ $usr['MBR_ID'] ]['children'] = [];
            }

            unset($usr);

            foreach($array as $usr) {
                if($usr['MBR_IDSPN'] != $usr['MBR_ID']) {
                    $structures[ $usr['MBR_IDSPN'] ]['children'][] = &$structures[ $usr['MBR_ID'] ];
                }
            }

            $firstKey = array_key_first($structures);
            return $structures[$firstKey];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }
}