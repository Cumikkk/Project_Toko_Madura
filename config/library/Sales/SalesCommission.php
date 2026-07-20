<?php
namespace App\Library\Sales;

use App\Models\SalesDivision;
use App\Models\SalesStructure;
use App\Models\Symbols;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class SalesCommission {

    public int $memberId;
    public array $memberData;
    protected array $commission = [
        'symbols' => [],
        'settings' => []
    ];

    public function __construct(int $_mbrid, array $_mbrdata) {
        $this->memberId = $_mbrid;
        $this->memberData = $_mbrdata;
    }

    private function rebateSetting(int $idSymbolCategory, int $idProduct): array {
        try {
            $db = Database::connect();
            $sqlGetRebateSetting = $db->query("SELECT * FROM tb_rebate_setting WHERE RSETTING_MBR = {$this->memberId} AND RSETTING_SYMCAT = {$idSymbolCategory} AND RSETTING_PRODUCT = {$idProduct} ORDER BY RSETTING_LEVEL ASC");
            return $sqlGetRebateSetting->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public function commissionSetting(int $idProduct): Self {
        try {
            $this->commission = [];
            if(empty($this->memberData)) {
                return $this;
            }
         
            $symbolCategory = Symbols::AllCategory();
            if(!$symbolCategory) {
                return $this;    
            }

            $salesData = SalesMain::getUserType($this->memberData['MBR_TYPE']);
            if(!$salesData) {
                return $this;
            }

            /** Get commission setting */
            $db = Database::connect();
            $sqlGetCommissionSetting = $db->query("SELECT * FROM tb_commset WHERE COMMSET_PRODUCT = {$idProduct} AND COMMSET_SALESCAT = {$salesData->salesDetail['SLSSTRC_DIV']}");
            $resultSqlCommissionSetting = $sqlGetCommissionSetting->fetch_all(MYSQLI_ASSOC) ?? [];

            /** build [symbols] array */
            foreach($symbolCategory as $symCat) {
                $defaultvalue = 0;
                $searchSymCatIndex = array_search($symCat['ID_SYMCAT'], array_column($resultSqlCommissionSetting, "COMMSET_SYMCAT"));
                if($searchSymCatIndex !== false) {
                    $defaultvalue = $resultSqlCommissionSetting[ $searchSymCatIndex ]['COMMSET_AMOUNT'] ?? 0;
                }

                $this->commission['symbols'][] = [
                    'id' => $symCat['ID_SYMCAT'],
                    'name' => $symCat['SYMCAT_NAME'],
                    'max' => $defaultvalue
                ];
            }

            /** build [settings] array */
            $structureDivision = SalesDivision::getStructure($salesData->salesDetail['SLSSTRC_DIV']);
            foreach($structureDivision as $sales) {
                $salesObject = [
                    'id' => $sales['ID_SLSSTRC'],
                    'name' => $sales['SLSSTRC_NAME'],
                    'code' => $sales['SLSSTRC_CODE'],
                    'level' => $sales['SLSSTRC_LEVEL'],
                    'amounts' => []
                ];

                foreach($symbolCategory as $symCat) {
                    /** Get rebate setting */
                    $defaultvalue = 0;
                    $rebateSetting = $this->rebateSetting($symCat['ID_SYMCAT'], $idProduct);
                    $searchRebateSettingValue = array_search($sales['ID_SLSSTRC'], array_column($rebateSetting, "RSETTING_SALES"));
                    if($searchRebateSettingValue !== FALSE) {
                        $defaultvalue = $rebateSetting[ $searchRebateSettingValue ]['RSETTING_AMOUNT'] ?? 0;
                    }

                    $salesObject['amounts'][] = [
                        'category_id' => $symCat['ID_SYMCAT'],
                        'category_name' => $symCat['SYMCAT_NAME'],
                        'amount' => $defaultvalue
                    ];
                }

                $this->commission['settings'][] = $salesObject; 
            }

            return $this;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return $this;
        }
    }

    public function get(): array {
        return $this->commission;
    }

    public function sum(int $idSymbolCategory): float {
        $total = 0;
        foreach($this->commission['settings'] as $setting) {
            foreach($setting['amounts'] as $amount) {
                if($amount['category_id'] != $idSymbolCategory) {
                    continue;
                }

                $total += $amount['amount'];
            }
        }

        return $total;
    }

    public function max(int $idSymbolCategory): float {
        $searchSymbol = array_search($idSymbolCategory, array_column($this->commission['symbols'], "id"));
        if($searchSymbol === false) {
            return 0;
        }

        return $this->commission['symbols'][$searchSymbol]['max'];
    }

    public function addOrUpdate(int $idSymbolCategory, int $idProduct, int $idSales, int $level, float $amount = 0): bool {
        try {
            global $db;
            if(!$db) {
                $db = Database::connect();
            }
            
            $column = [
                'RSETTING_MBR',
                'RSETTING_SYMCAT',
                'RSETTING_PRODUCT',
                'RSETTING_SALES',
                'RSETTING_LEVEL',
                'RSETTING_AMOUNT',
                'RSETTING_DATETIME',
            ];

            $updateColumn = [
                'RSETTING_AMOUNT = ?'
            ];

            $datetime = date("Y-m-d H:i:s");
            $sqlInsertOrUpdate = $db->prepare("INSERT INTO tb_rebate_setting (".implode(", ", $column).") VALUES (".implode(",", array_fill(0, count($column), "?")).") ON DUPLICATE KEY UPDATE ".implode(", ", $updateColumn)."");
            $sqlInsertOrUpdate->bind_param("iiiiidsd", $this->memberId, $idSymbolCategory, $idProduct, $idSales, $level, $amount, $datetime, $amount);
            $execute = $sqlInsertOrUpdate->execute();
            if(!$execute) {
                return false;
            }

            return true;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}