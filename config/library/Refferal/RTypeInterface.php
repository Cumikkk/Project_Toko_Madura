<?php
namespace App\Library\Refferal;

interface RTypeInterface {

    
    public function upline(): array|bool;
    public function validate(): bool;
    public function apply(int $user_id): bool;

}