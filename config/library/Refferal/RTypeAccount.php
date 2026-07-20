<?php
namespace App\Library\Refferal;

use App\Library\Sales\SalesMain;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Refferal;
use App\Models\User;
use Config\Core\Database;
use Exception;

class RTypeAccount implements RTypeInterface {

    public string $refferalCode;
    public array $upline = [];

    public bool $isValid = false;

    public function __construct(string $code) {
        $this->refferalCode = $code;
    }

    public function upline(): array|bool {
        return $this->upline;
    }

    public function validate(): bool {
        $explode = explode("-", $this->refferalCode);
        if(count($explode) != 3) {
            return false;
        }

        /** check key */
        $requiredKeys = [0, 1, 2];
        foreach($requiredKeys as $key) {
            if(!array_key_exists($key, $explode)) {
                return false;
            }
        } 

        /** check suffix */
        $checkSuffix = AccountType::findBySuffix($explode[0]);
        if(!$checkSuffix ) {
            return false;
        }

        if($checkSuffix['RTYPE_STS'] != -1) {
            return false;
        }

        /** check product type */
        if(Refferal::parseProductType($checkSuffix['RTYPE_TYPE']) != $explode[1]) {
            return false;
        }

        /** check userdata */
        $userdata = User::findByCode($explode[2]);
        if(!$userdata) {
            return false;
        }

        $this->upline = $userdata;
        $this->isValid = true;
        
        return true;
    }

    public function apply(int $user_id): bool {
        $explode = explode("-", $this->refferalCode);
        if(!$this->isValid) {
            return false;
        }

        if(empty($this->upline)) {
            return false;
        }

        $salesData = SalesMain::getUserType($this->upline['MBR_TYPE']);
        if(!$salesData) {
            return false;
        }

        if(!$salesData->isCanShareRefferal()) {
            return false;
        }

        return Database::update("tb_member", ['MBR_SUFFIX' => $explode[0]], ['MBR_ID' => $user_id]);
    }
}