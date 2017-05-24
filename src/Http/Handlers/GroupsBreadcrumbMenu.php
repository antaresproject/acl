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

use Antares\Foundation\Support\MenuHandler;

class GroupsBreadcrumbMenu extends MenuHandler
{

    /**
     * Menu configuration.
     *
     * @var array
     */
    protected $menu = [
        'id'    => 'roles',
        'title' => 'Groups',
        'link'  => 'antares::acl',
        'icon'  => null,
        'boot'  => [
            'group' => 'menu.top.roles',
            'on'    => 'antares/acl::roles.index'
        ]
    ];

    /**
     * Get the title.
     * @param  string  $value
     * @return string
     */
    public function getTitleAttribute($value)
    {
        return $this->container->make('translator')->trans($value);
    }

    /**
     * Create a handler.
     * @return void
     */
    public function handle()
    {
        $acl           = app('antares.acl')->make('antares/acl');
        $canCreateRole = $acl->can('create-role');
        if (!$canCreateRole) {
            return;
        }
        $this->createMenu();

        if ($canCreateRole) {
            $this->handler
                    ->add('role-add', '^:roles')
                    ->title('Add Group')
                    ->icon('zmdi-plus-circle-o')
                    ->link(handles('antares::acl/index/roles/create'));
        }
    }

}
