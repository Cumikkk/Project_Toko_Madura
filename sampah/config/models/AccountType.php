<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class AccountType {

    public static function findById(int $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racctype WHERE ID_RTYPE = {$id} LIMIT 1");
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


    public static function findBySuffix(string $suffix): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racctype WHERE RTYPE_SUFFIX = '{$suffix}' LIMIT 1");
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

    public static function findByType(array $type = []): array {
        try {
            if(empty($type)) {
                return [];
            }

            $type = implode(",", $type);
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racctype WHERE FIND_IN_SET(UPPER(RTYPE_TYPE), UPPER('{$type}')) > 0");
            if($sqlGet->num_rows <= 0) {
                return [];
            }

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function all(): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racctype");
            if($sqlGet->num_rows <= 0) {
                return [];
            }

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

}