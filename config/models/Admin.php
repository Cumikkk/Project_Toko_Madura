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

            $select = $db->query("SELECT (SELECT (MAX(id_users) + 1) FROM users) as ADM_ID");
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

            $sqlCheck = $db->query("SELECT * FROM users WHERE id_users = {$idAdm} AND role = 'master' LIMIT 1");
            if($sqlCheck->num_rows != 1) {
                return [];
            }
            $rawUser = $sqlCheck->fetch_assoc();

            // Determine dynamic ADM_LEVEL based on permissions in admin_authorize
            // Level 1 = Programmer (has Developer modules 5/6 permissions)
            // Level 2 = Master Owner (has Admin module 4 permissions)
            // Level 3 = Admin Staf (only basic modules 1/2/3 permissions)
            $level = 3;
            $sqlPerms = $db->query("
                SELECT ap.module_id 
                FROM admin_authorize aa 
                JOIN admin_permissions ap ON (ap.id = aa.permission_id) 
                WHERE aa.admin_id = {$idAdm} AND (aa.status = -1 OR aa.status = 1)
            ");
            if ($sqlPerms && $sqlPerms->num_rows > 0) {
                $modIds = [];
                while ($pRow = $sqlPerms->fetch_assoc()) {
                    $modIds[] = intval($pRow['module_id']);
                }
                if (in_array(5, $modIds) || in_array(6, $modIds)) {
                    $level = 1;
                } elseif (in_array(4, $modIds)) {
                    $level = 2;
                } else {
                    $level = 3;
                }
            }

            return [
                'ID_ADM' => $rawUser['id_users'],
                'ADM_ID' => $rawUser['id_users'],
                'ADM_NAME' => $rawUser['nama_lengkap'],
                'ADM_USER' => $rawUser['username'],
                'ADM_EMAIL' => $rawUser['email'],
                'ADM_PHONE' => $rawUser['no_hp'],
                'ADM_PASS'  => $rawUser['password'],
                'ADM_LEVEL' => $level,
                'ADM_STS' => 1,
                'ADMROLE_NAME' => ($level == 1) ? 'Programmer' : (($level == 2) ? 'Master' : 'Admin Staf'),
                'role' => $rawUser['role'],
                'ADM_COUNTRY' => 7
            ];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    } 

    public static function adminRoles() {
        return [
            ['ID_ADMROLE' => 1, 'ADMROLE_NAME' => 'Master', 'ADMROLE_STS' => -1]
        ];
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