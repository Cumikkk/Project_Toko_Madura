<?php
namespace App\Factory;

use Allmedia\Shared\AdminPermission\Models\PermissionModule;
use Config\Core\Database;

class PermissionModuleFactory {

    public static function init(?Database $db = null): PermissionModule {
        $db ??= new Database();
        return new PermissionModule($db);
    }

}