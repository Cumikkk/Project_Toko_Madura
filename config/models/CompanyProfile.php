<?php
    namespace App\Models;
    use Config\Core\Database;
    use Exception;

class CompanyProfile {

    public static $name;

    public static function init() {
        self::$name = $_ENV['APP_NAME'];
    }

    public static function profilePerusahaan(int $defaultId = 1) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_profile WHERE ID_PROF = $defaultId LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return [];
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            if(ini_get("dsplay_errors") == "1") {
                throw $e;
            }

            return [];
        }
    } 
    
    public static function getMainOffice(int $defaultId = 1) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_office WHERE ID_OFC = $defaultId LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return [];
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            if(ini_get("dsplay_errors") == "1") {
                throw $e;
            }

            return [];
        }
    }
}