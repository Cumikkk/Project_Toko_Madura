<?php
namespace App\Library\InternalTransfer;

use App\Library\InternalTransfer\TransferData;

interface TransferInterface {

    public function execute(): TransferData;
    
}