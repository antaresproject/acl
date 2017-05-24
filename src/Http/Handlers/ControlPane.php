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

class ControlPane extends LeftPane
{

    /**
     * Handle pane for dashboard page.
     *
     * @return void
     */
    public function compose($name = null, $options = array())
    {

        $menu = app('antares.widget')->make('menu.control.pane');
        $auth = app('auth');

        $acl                  = app('antares.acl')->make('antares/acl');
        $canAdministratorList = $auth->is('super-administrator') && $acl->can('admin-list');
        $canRoleList          = $acl->can('roles-list');
        if (!$canAdministratorList and ! $canRoleList) {
            return;
        }

        $menu->add('general-settings')
                ->link(handles('antares::settings/index'))
                ->title(trans('System'))
                ->icon('zmdi-settings');


        $pane = app()->make('antares.widget')->make('pane.left');
        $pane->add('control')->content(view('antares/acl::partial._control_pane'));
    }

}
