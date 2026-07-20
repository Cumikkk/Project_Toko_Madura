<?php 
namespace Config\Core;

use Exception;

class AdminAuth {

    public static string $sessionAuthName = "token";

    public static function logout() {
        global $_SESSION, $_COOKIE;
        $_SESSION['token_admin'] = "";
        $_COOKIE['token_admin'] = "";

        session_destroy();
        setcookie("token_admin", "");
        return true;
    }

    public static function setSessionData(array $data): bool {
        global $_SESSION, $_COOKIE;
        if(empty($data['token'])) {
            return false;
        }

        $_SESSION[ self::$sessionAuthName ] = $_COOKIE[ self::$sessionAuthName ] = $data['token'];
        return true;
    }

    public static function getSessionData(): array|bool {
        global $_SESSION, $_COOKIE;
        $token = $_SESSION[ self::$sessionAuthName ] ?? $_COOKIE[ self::$sessionAuthName ] ?? "";
        if(empty($token)) {
            return false;
        }

        return [
            'token' => $token
        ];
    }

    public static function authentication() {
        try {
            global $db, $_SESSION, $_COOKIE;
            if(empty($db)) {
                $db = Database::connect();
            }
            
            $authData = self::getSessionData();
            if(!$authData) {
                return false;
            }

            /** Check Database */
            $token = $authData['token'];
            $sqlCheck = $db->query("
                SELECT 
                    * 
                FROM tb_admin 
                JOIN tb_admin_role tar ON (tar.ID_ADMROLE = ADM_LEVEL) 
                JOIN tb_country tc ON (tc.ID_COUNTRY = ADM_COUNTRY)
                WHERE ADM_TOKEN = '{$token}' 
                LIMIT 1
            ");

            $user = $sqlCheck->fetch_assoc(); 
            if($sqlCheck->num_rows != 1) {
                return false;
            }

            
            /** Check Expired */
            if(strtotime($user['ADM_TOKEN_EXPIRED']) < strtotime("now")) {
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
}