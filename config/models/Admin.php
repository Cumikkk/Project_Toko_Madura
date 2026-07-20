<?php
namespace App\Models;

use Config\Core\AdminAuth;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Admin extends AdminAuth {

    public static function createAdmId(): int {
        try {
            global $db;
            if(empty($db)) {
                $db = Database::connect();
            }

            $select = $db->query("SELECT (SELECT (MAX(tb2.ADM_ID) + 1) FROM tb_admin as tb2) as ADM_ID");
            return $select->fetch_assoc()['ADM_ID'] ?? 0;

        } catch (Exception $e) {
            return 0;
        }
    }

    public static function findById(int $idAdm): array {
        try {
            global $db;
            if(empty($db)) {
                $db = Database::connect();
            }

            /** Check Database */
            $dateNow = date("Y-m-d H:i:s");
            $sqlCheck = $db->query("SELECT * FROM tb_admin JOIN tb_admin_role tar ON (tar.ID_ADMROLE = ADM_LEVEL) WHERE ID_ADM = {$idAdm} LIMIT 1");
            $user = $sqlCheck->fetch_assoc(); 
            if($sqlCheck->num_rows != 1) {
                return [];
            }

            return $user;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    } 

    public static function adminRoles() {
        try {
            global $db;
            $user = Self::authentication() ?? [];
            if(empty($user)) {
                return [];
            }

            $currentLevel = $user['ADM_LEVEL'];
            $sqlGet = $db->query("SELECT * FROM tb_admin_role WHERE (ID_ADMROLE > {$currentLevel} OR {$currentLevel} = 1) AND {$currentLevel} <= 2 AND ADMROLE_STS = -1");
            return $sqlGet->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getAdminBank(string $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_bankadm WHERE MD5(MD5(ID_BKADM)) = '{$id}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            
            return false;
        }
    }

    public static function findBankByCurrency(string $currency): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_bankadm WHERE LOWER(BKADM_CURR) = LOWER('{$currency}') LIMIT 1");
            return $sqlGet->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            
            return [];
        }
    }
    
}