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
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Acl;

use Antares\Foundation\Support\Providers\ModuleServiceProvider;
use Antares\Acl\Http\Handlers\GroupsBreadcrumbMenu;
use Antares\Acl\Http\Handlers\UsersBreadcrumbMenu;
use Antares\Acl\Http\Handlers\ModulesPane;
use Antares\Acl\Http\Handlers\ControlPane;
use Antares\Acl\Http\Handlers\StaffPane;

class AclServiceProvider extends ModuleServiceProvider
{

    /**
     * The application or extension namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Antares\Acl\Http\Controllers';

    /**
     * The application or extension group namespace.
     *
     * @var string|null
     */
    protected $routeGroup = 'antares/acl';

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindContracts();
    }

    /**
     * Boot extension components.
     *
     * @return void
     */
    protected function bootExtensionComponents()
    {
        $path = __DIR__ . '/../resources';
        $this->addConfigComponent('antares/acl', 'antares/acl', "{$path}/config");
        $this->addLanguageComponent('antares/acl', 'antares/acl', "{$path}/lang");
        $this->addViewComponent('antares/acl', 'antares/acl', "{$path}/views");
        $this->bootMenu();
        $this->bootMemory();
    }

    /**
     * Boot extension routing.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $path = __DIR__;

        $this->loadBackendRoutesFrom("{$path}/Http/backend.php");
    }

    /**
     * booting menu
     */
    protected function bootMenu()
    {
        $view = $this->app->make('view');
        $view->composer('antares/acl::acl.*', function () {
            return ModulesPane::getInstance()->make();
        });
        $this->attachMenu([GroupsBreadcrumbMenu::class, UsersBreadcrumbMenu::class]);
        $view->composer('antares/foundation::settings.*', ControlPane::class);
        $view->composer(['antares/acl::users.index', 'antares/acl::roles.index'], StaffPane::class);
    }

    /**
     * booting acl memory
     */
    protected function bootMemory()
    {
        $this->app->make('antares.acl')->make($this->routeGroup)->attach(
                $this->app->make('antares.platform.memory')
        );
    }

}
