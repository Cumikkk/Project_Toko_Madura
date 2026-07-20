<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class SalesStructure {

    public static $typeTrader = 0;
    
    public static function structure(): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_structure ORDER BY ID_SLSSTRC ASC");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return [];
        }
    }

    public static function findByIdHash(string $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_structure WHERE MD5(MD5(ID_SLSSTRC)) = '{$id}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return false;
        }
    }

    public static function findById(int $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_structure WHERE ID_SLSSTRC = {$id} LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return false;
        }
    }

    public static function findByCode(string $code): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_structure WHERE SLSSTRC_CODE = '{$code}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return false;
        }
    }

}