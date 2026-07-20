<?php
namespace App\Factory;

use Allmedia\Shared\AdminPermission\Models\PermissionGroup;
use Config\Core\Database;

class PermissionGroupFactory {

    public static function init(?Database $db = null): PermissionGroup {
        $db ??= new Database();
        return new PermissionGroup($db);
    }

}