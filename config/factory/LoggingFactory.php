<?php
namespace App\Factory;

class LoggingFactory {

    public static function make(?string $dir = null): \Config\Core\Logging
    {
        return new \Config\Core\Logging($dir ?? CONFIG_ROOT . "/logs/systems");
    }
}