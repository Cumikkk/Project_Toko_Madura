<?php
namespace App\Library\Refferal;

use App\Models\AccountType;
use App\Models\Refferal;
use App\Models\User;
use Config\Core\Database;

class RTypeGroup implements RTypeInterface {

    public string $refferalCode;
    public array $upline = [];

    public function __construct(string $code) {
        $this->refferalCode = $code;
    }

    public function validate(): bool {
        $explode = explode("-", $this->refferalCode);
        if(count($explode) != 2) {
            return false;
        }

        if(!array_key_exists(1, $explode)) {
            return false;
        }

        /** check account hash */
        $accData = Refferal::findGroupRefferal($explode[1]);
        if(!$accData) {
            return false;
        }

        $userdata = User::findByMemberId($accData['MBR_ID']);
        if(!$userdata) {
            return false;
        }

        if(Refferal::parseProductType($accData['RTYPE_TYPE']) != $explode[0]) {
            return false;
        }

        $this->upline = $userdata;

        return true;
    }

    public function upline(): array|bool {
        return $this->upline;
    }

    public function apply(int $user_id): bool {
        $explode = explode("-", $this->refferalCode);
        if(count($explode) != 2 || !array_key_exists(1, $explode)) {
            return false;
        }

        /** check account hash */
        $accData = Refferal::findGroupRefferal($explode[1]);
        if(!$accData) {
            return false;
        }

        /** get account suffix by type */
        $accType = AccountType::findByType([$accData['RTYPE_TYPE']]);
        if(empty($accType)) {
            return false;
        }
        
        $accountSuffix = array_map(fn($ar): string => $ar['RTYPE_SUFFIX'], $accType);
        if(empty($accountSuffix)) {
            return false;
        }

        return Database::update("tb_member", ['MBR_SUFFIX' => implode(",", $accountSuffix)], ['MBR_ID' => $user_id]);
    }
}