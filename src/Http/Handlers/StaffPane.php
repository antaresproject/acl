<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Access Control
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Acl\Http\Handlers;

use Antares\Foundation\Http\Composers\LeftPane;

class StaffPane extends LeftPane
{

    /**
     * Handle pane for dashboard page.
     *
     * @return void
     */
    public function compose($name = null, $options = array())
    {
        $menu                 = app('antares.widget')->make('menu.acl.staff.pane');
        $acl                  = app('antares.acl')->make('antares/acl');
        $canAdministratorList = $acl->can('admin-list');

        $canRoleList = $acl->can('roles-list');
        if (!$canAdministratorList and ! $canRoleList) {
            return;
        }
        if ($canRoleList) {
            $menu->add('groups')
                    ->link(handles('antares::acl/index/roles'))
                    ->title(trans('Groups'))
                    ->icon('zmdi-accounts-list-alt')
                    ->active(!is_null(from_route('roles')) or request()->segment(4) === 'roles' or request()->segment(3) === 'roles');
        }
        if ($canAdministratorList) {
            $menu->add('users')
                    ->link(handles('antares::acl/index/users'))
                    ->title(trans('Users'))
                    ->icon('zmdi-accounts-list')
                    ->active(!is_null(from_route('user')) or request()->segment(4) === 'users' or request()->segment(3) === 'users');
        }
        $pane = app('antares.widget')->make('pane.left');
        $pane->add('control')->content(view('antares/acl::partial._staff_pane'));
    }

}
