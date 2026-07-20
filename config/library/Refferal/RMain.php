<?php
namespace App\Library\Refferal;

class RMain {

    public static function refferalType(string $refferalCode): RTypeUser|RTypeGroup|RTypeAccount|bool {
        $explode = explode("-", $refferalCode);
        switch(count($explode)) {
            case 1: return new RTypeUser($refferalCode);
            // case 2: return new RTypeGroup($refferalCode);
            case 3: return new RTypeAccount($refferalCode);
        }

        return false;
    }
}