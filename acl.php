<?php

use Antares\Acl\RoleActionList;
use Antares\Model\Role;
use Antares\Acl\Action;

$presentationActions = [
    'Admin List', //users
    'Roles List', //roles
];

$crudActions = [
    'User Create', 'User Update', 'User Delete', //users
    'Create Role', 'Edit Role', 'Delete Role', //roles
    'Properties', 'Properties Update', //action properties
    'Manage Roles', 'Manage Acl', 'Manage Settings', //global
    'Login As User', 'Manage Levels'
];

$actions    = [];
$allActions = array_merge($presentationActions, $crudActions);

foreach ($allActions as $actionName) {
    $actions[] = new Action('', $actionName);
}

$permissions = new RoleActionList;
$permissions->add(Role::admin()->name, $actions);

return $permissions;
