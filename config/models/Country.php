<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Country {

    public static function countries(): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_country ORDER BY COUNTRY_NAME");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getByName($countryName): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_country WHERE LOWER(COUNTRY_NAME) = LOWER('{$countryName}')");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function getByCountryCode($countryCode): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_country WHERE COUNTRY_CODE = '{$countryCode}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}