<?php
namespace App\Library\InternalTransfer;

use App\Models\Account;
use App\Models\User;

class TransferAdapter {
 
    /** Requesting data */
    public int $mbrid;
    public string $login;


    /** Data consumed */
    public float $rate = 0;
    public string $currency = '';
    public float $balance = 0;

    public function __construct(int $mbrid, string $login) 
    {
        $this->login = $login;
        $this->mbrid = $mbrid;
        $this->extract();
    }
    
    public function isValid(): bool 
    {
        return ($this->login != '' && $this->currency != '' && $this->rate > 0);
    }

    protected function extract(): void 
    {
        switch(strtolower($this->login) == "wallet") {
            case true :
                $this->currency = "USD";
                $this->rate = 1;
                $this->balance = User::wallet($this->mbrid);
                return;

            case false :
                $account = Account::realAccountDetail_byLogin($this->login);
                if(!$account || $account['ACC_MBR'] != $this->mbrid) {
                    return;
                }

                $this->currency = $account['RTYPE_META_CURR'];
                $this->rate = $account['RTYPE_RATE'];
                $this->balance = (float) Account::marginBalance($account['ACC_LOGIN']);
                break;
        }
    }

}