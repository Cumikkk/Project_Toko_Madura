<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Wilayah {

    public static function provinces(): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT KDP_PROV FROM tb_kodepos GROUP BY KDP_PROV");
            $result = $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];
            return array_values(array_column($result, "KDP_PROV"));

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function regency(string $province): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT KDP_KABKO FROM tb_kodepos WHERE UPPER(KDP_PROV) = UPPER('{$province}') GROUP BY KDP_KABKO");
            $result = $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];
            return array_values(array_column($result, "KDP_KABKO"));

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function district(string $regency): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT KDP_KECAMATAN FROM tb_kodepos WHERE UPPER(KDP_KABKO) = UPPER('{$regency}') GROUP BY KDP_KECAMATAN");
            $result = $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];
            return array_values(array_column($result, "KDP_KECAMATAN"));

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function villages(string $district): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT KDP_KELURAHAN, KDP_POS FROM tb_kodepos WHERE UPPER(KDP_KECAMATAN) = UPPER('{$district}') GROUP BY KDP_KELURAHAN");
            $result = $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];
            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function postalCode(string $province, string $regency, string $district, string $villages, string $postalCode): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_kodepos WHERE UPPER(KDP_PROV) = UPPER('{$province}') AND UPPER(KDP_KABKO) = UPPER('{$regency}') AND UPPER(KDP_KECAMATAN) = UPPER('{$district}') AND UPPER(KDP_KELURAHAN) = UPPER('{$villages}') AND KDP_POS = $postalCode LIMIT 1");
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