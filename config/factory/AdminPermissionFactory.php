<?php
namespace App\Factory;

use Allmedia\Shared\AdminPermission\Core\AdminPermissionCore;
use Config\Core\Database;

class AdminPermissionFactory {

    public static function adminPermissionCore(?Database $db = null): AdminPermissionCore {
        $db ??= new Database();
        return new AdminPermissionCore($db);
    }

}