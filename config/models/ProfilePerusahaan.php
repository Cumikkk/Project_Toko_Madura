<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class ProfilePerusahaan {

    public static string $emailDealing = "alfiyandzulfikri@gmail.com"; 
    public static string $namaDealing = "Dealing RRFX"; 

    public static function get($id = 1) {
        /** Profile Perusahaan */
        $profilePerusahaan = CompanyProfile::profilePerusahaan();
        if(!is_array($profilePerusahaan)) {
            $profilePerusahaan = [];
        }
        
        /** Get Main Office */
        $office = CompanyProfile::getMainOffice();
        if(!is_array($office)) {
            $office = [];
        }

        return array_merge(
            $profilePerusahaan,
            [
                'OFFICE' => $office
            ]
        );
    }

    public static function office(): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_office");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function wpb_verifikator_search_bynme($name) {
        $db = Database::connect();
        $sql_get_wpb = mysqli_query($db, "SELECT * FROM tb_wpb WHERE WPB_NAMA = '$name' LIMIT 1");
        if($sql_get_wpb) {
            return mysqli_fetch_assoc($sql_get_wpb);
        }

        return [];
    } 

    public static function wpb_verifikator() {
        $db = Database::connect();
        $sql_get_wpb = mysqli_query($db, "SELECT WPB_NAMA FROM tb_wpb WHERE WPB_STS = -1 AND WPB_VERIFY = -1 LIMIT 1");
        if($sql_get_wpb) {
            return mysqli_fetch_assoc($sql_get_wpb);
        }

        return [];
    } 

    public static function list_wpb($type = 1, $chunk = 16, $status = -1) {
        global $db;
        $sql_get_wpb = mysqli_query($db, "SELECT * FROM tb_wpb WHERE WPB_STS = {$status}");
        $list_wpb = [];

        if($sql_get_wpb) {
            foreach(mysqli_fetch_all($sql_get_wpb, MYSQLI_ASSOC) as $wpb) {
                if($type == -1) {
                    $list_wpb[] = $wpb;
                }else {
                    if($wpb['WPB_TYPE'] == $type) {
                        $list_wpb[] = $wpb;
                    }
                }
            }
        }

        return ($chunk != 0) ? array_chunk($list_wpb, $chunk) : $list_wpb;
    }

    public static function rateWdBonus(): float {
        $profilePerusahaan = self::get();
        if(!$profilePerusahaan) {
            return 0;
        }

        return $profilePerusahaan['RATE_WD_BONUS'] ?? 0;
    }

    public static function limitAccountMargin(): float {
        $profilePerusahaan = self::get();
        if(!$profilePerusahaan) {
            return 0;
        }

        return $profilePerusahaan['ACCOUNT_MARGIN_LIMIT'] ?? 0;
    }
}