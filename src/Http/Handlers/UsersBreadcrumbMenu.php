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

class UsersBreadcrumbMenu extends MenuHandler
{

    /**
     * Menu configuration.
     *
     * @var array
     */
    protected $menu = [
        'id'    => 'users',
        'title' => 'Users',
        'link'  => 'antares::acl/users',
        'icon'  => null,
        'boot'  => [
            'group' => 'menu.top.users',
            'on'    => 'antares/acl::users.index'
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
        $canCreateUser = $acl->can('user-create');
        if (!$canCreateUser) {
            return;
        }
        $this->createMenu();

        if ($canCreateUser) {
            $this->handler
                    ->add('user-add', '^:users')
                    ->title('Add User')
                    ->icon('zmdi-plus-circle-o')
                    ->link(handles('antares::acl/users/create'));
        }
    }

}
