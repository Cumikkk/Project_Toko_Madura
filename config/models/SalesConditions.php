<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class SalesConditions {

    public static function findById(int $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_conditions WHERE ID_SLSCONDITION = {$id} LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            
            return false;
        }     
    }

    public static function findByIdHash(string $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_sales_conditions WHERE MD5(MD5(ID_SLSCONDITION)) = '{$id}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            
            return false;
        }     
    }

    public static function commissions(int $idSalesCondition): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    upline.*,
                    tsc.SLSCOM_FOREX,
                    tsc.SLSCOM_GOLD,
                    tsc.SLSCOM_INDEX
                FROM tb_sales_commission as tsc
                JOIN (
                    SELECT
                        tm.MBR_ID,
                        tm.MBR_NAME,
                        tm.MBR_CODE,
                        tm.MBR_TYPE,
                        IFNULL(tss.SLSSTRC_NAME, 'Trader') as sales_type
                    FROM tb_member as tm
                    LEFT JOIN tb_sales_structure as tss ON (tss.ID_SLSSTRC = tm.MBR_TYPE)
                ) as upline ON (upline.MBR_ID = tsc.SLSCOM_MBR)
                WHERE tsc.SLSCOM_IDCONDITION = {$idSalesCondition} 
                ORDER BY tsc.ID_SLSCOM ASC
            ");

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        }  catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
            
            return [];
        }
    }

}