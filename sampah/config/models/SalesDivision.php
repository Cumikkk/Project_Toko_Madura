<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class SalesDivision {

    public static function findById(int $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_division WHERE ID_SLSDIVISION = {$id} LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return false;
        }
    }

    public static function getStructure(int $idDivision): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_structure WHERE SLSSTRC_DIV = {$idDivision} ORDER BY SLSSTRC_LEVEL ASC");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            return [];
        }
    }

}