<?php
namespace App\Library\Sales;

use App\Models\SalesDivision;
use App\Models\SalesStructure;
use App\Models\Symbols;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class SalesData {

    public $idSales;
    public $salesDetail;

    public function __construct(int $_idSales, array $_salesDetail = []) {
        $this->idSales = $_idSales;
        $this->salesDetail = $_salesDetail;
    }

    public function code(): string {
        return $this->salesDetail['SLSSTRC_CODE'] ?? "";
    }
    
    public function getId(): bool|int {
        return $this->idSales ?? false;
    }

    public function isCanShareRefferal(): bool {
        return $this->salesDetail['SLSSTRC_REF'] ?? false;
    }

    public function isCanConfigureCommission(): bool {
        return ($this->salesDetail['SLSSTRC_LEVEL'] ?? false) == 0;
    }
    
    public function isCanKeepDorman(): bool {
        return $this->salesDetail['SLSSTRC_RETENTION'] ?? false;
    }
    
    public function level(): int {
        return $this->salesDetail['SLSSTRC_LEVEL'];
    }

    public function getUp(): int {
        return $this->salesDetail['SLSSTRC_UP'];
    }

    public function division(): bool|array {
        return SalesDivision::findById($this->salesDetail['SLSSTRC_DIV']);
    }

    public function isHeadOfStructure(): bool {
        return $this->salesDetail['SLSSTRC_LEVEL'] == 0;
    }

    public function isAllowToRequestAccountCondition(): bool {
        return (bool) ($this->salesDetail['SLSSTRC_ACCOUNT_CONDITION'] ?? false);
    } 

}