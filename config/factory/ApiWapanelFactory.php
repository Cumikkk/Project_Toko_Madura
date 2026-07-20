<?php
namespace App\Factory;

use Config\Core\ApiWapanel;

class ApiWapanelFactory {

    public static function init(): ApiWapanel {
        return new ApiWapanel();
    }

}
