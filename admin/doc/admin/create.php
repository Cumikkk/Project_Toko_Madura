<?php
Allmedia\Shared\AdminPermission\SharedViews::render("admins/create", [
    'isAllowToCreate' => $adminPermissionCore->isHavePermission($moduleId, "create"),
    'countries' => App\Models\Country::countries(),
    'adminRoles' => App\Models\Admin::adminRoles()
]);