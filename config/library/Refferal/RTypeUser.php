<?php
namespace App\Library\Refferal;

use App\Library\Sales\SalesMain;
use App\Models\User;
use Config\Core\Database;

class RTypeUser implements RTypeInterface {

    public string $refferalCode;
    public array $upline = [];

    public function __construct(string $code) {
        $this->refferalCode = $code;
    }

    public function validate(): bool {
        $explode = explode("-", $this->refferalCode);
        if(count($explode) != 1) {
            return false;
        }

        /** check user code */
        $userdata = User::findByCode($this->refferalCode);
        if(!$userdata) {
            return false;
        }

        $this->upline = $userdata;

        return true;
    }

    public function upline(): array|bool {
        return $this->upline;
    }

    public function apply(int $user_id): bool {
        if(empty($this->upline)) {
            return false;
        }

        $salesData = SalesMain::getUserType($this->upline['MBR_TYPE']);
        if($salesData) {
            if(!$salesData->isCanShareRefferal()) {
                return false;
            }
        }

        /** Jika upline punya default sendiri (MBR_SUFFIX)*/
        if(!empty($this->upline['MBR_SUFFIX'])) {
            return Database::update("tb_member", ['MBR_SUFFIX' => $this->upline['MBR_SUFFIX']], ['MBR_ID' => $user_id]);
        }

        /** Jika tidak, set ke default dari account category (internal, external) */
        return true;
    }
}