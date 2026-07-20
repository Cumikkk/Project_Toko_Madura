<?php
namespace App\Library\Sales;

use App\Library\Sales\Offline\SBranchManager;
use App\Library\Sales\Offline\SBusinessDevelopment;
use App\Library\Sales\Offline\SFinancialMarketing;
use App\Library\Sales\Offline\SHeadBusinessDev;
use App\Library\Sales\Offline\SHeadOfSales;
use App\Models\ProfilePerusahaan;
use App\Models\SalesStructure;
use App\Models\User;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class SalesMain {

    public static function getUserType(int $mbrType): SalesData|bool {
        try {
            $structure = SalesStructure::findById($mbrType);
            if(!$structure) {
                return false;
            }

            $salesData = new SalesData($mbrType, $structure);
            if(!$salesData->idSales) {
                return false;
            }

            return $salesData;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw new Exception("Invalid Sales Type");
            }

            return false;
        }
    }
    
    public static function salesCommission(int $mbrid): SalesCommission|bool {
        try {
            $userData = User::findByMemberId($mbrid);
            if(!$userData) {
                return false;
            }

            $salesCommission = new SalesCommission($mbrid, $userData);
            if(!$salesCommission->memberId) {
                return false;
            }

            return $salesCommission;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw new Exception("Invalid Sales Type");
            }

            return false;
        }
    }

    public static function rebateSetting(int $mbrid, int $idSymbolCategory, int $idProduct, int $idSales): array|bool {
        try {
            $db = Database::connect();
            $sqlGetRebateSetting = $db->query("SELECT * FROM tb_rebate_setting WHERE RSETTING_MBR = {$mbrid} AND RSETTING_SYMCAT = {$idSymbolCategory} AND RSETTING_PRODUCT = {$idProduct} AND RSETTING_SALES = {$idSales} LIMIT 1");
            if($sqlGetRebateSetting->num_rows != 1) {
                return false;
            }
            
            return $sqlGetRebateSetting->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findMyHeadOfSales(int $mbrid): SalesCommission|bool {
        try {
            $db = Database::connect();
            $sqlGetHeadOfSales = $db->query("
                WITH RECURSIVE member_hierarchy AS (
                    SELECT 
                        MBR_ID,
                        MBR_NAME,
                        MBR_CODE,
                        MBR_IDSPN,
                        MBR_EMAIL,
                        MBR_TYPE
                    FROM tb_member
                    WHERE MBR_ID = {$mbrid}
                    UNION ALL
                    SELECT 
                        m.MBR_ID,
                        m.MBR_NAME,
                        m.MBR_CODE,
                        m.MBR_IDSPN,
                        m.MBR_EMAIL,
                        m.MBR_TYPE
                    FROM tb_member m
                    INNER JOIN member_hierarchy mh ON m.MBR_ID = mh.MBR_IDSPN
                )
                    
                SELECT 
                    mh.*,
                    IFNULL(tss.SLSSTRC_NAME, 'Trader') as SALES_TYPE 
                FROM member_hierarchy mh
                LEFT JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = mh.MBR_TYPE)
                WHERE mh.MBR_ID != 1000000000
                AND tss.ID_SLSSTRC IS NOT NULL
                ORDER BY tss.SLSSTRC_LEVEL
                LIMIT 1
            ");

            if($sqlGetHeadOfSales->num_rows != 1) {
                echo 1;
                return false;
            }

            $headOfSales = $sqlGetHeadOfSales->fetch_assoc();
            $salesCommission = self::salesCommission($headOfSales['MBR_ID']);
            return $salesCommission;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function searchNmi(int $receiverMbrId, array $listOfMbrid = [], ?string $dateStart = null, ?string $dateEnd = null): NmiData|bool {
        try {
            $rateWdBonus = ProfilePerusahaan::rateWdBonus();
            return new NmiData($receiverMbrId, $listOfMbrid, $dateStart, $dateEnd, $rateWdBonus);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}