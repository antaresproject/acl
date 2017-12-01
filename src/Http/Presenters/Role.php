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

namespace Antares\Acl\Http\Presenters;

use Antares\Acl\Http\Datatables\Roles as Datatables;
use Antares\Acl\Http\Form\Role as RoleForm;
use Antares\Acl\Http\Breadcrumb\Breadcrumb;
use Antares\Acl\Contracts\ModulesAdapter;
use Antares\Model\Role as Eloquent;
use Illuminate\Container\Container;

class Role extends Presenter
{

    /**
     * application container
     * 
     * @var Container
     */
    protected $container;

    /**
     * modules adapter instance
     *
     * @var ModulesAdapter
     */
    protected $adapter;

    /**
     * breadcrumbs instance
     *
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * datatables instance
     *
     * @var Datatables
     */
    protected $datatables;

    /**
     * Create a new Role presenter.
     * 
     * @param Container $container
     * @param ModulesAdapter $adapter
     * @param Breadcrumb $breadcrumb
     */
    public function __construct(Container $container, ModulesAdapter $adapter, Breadcrumb $breadcrumb, Datatables $datatables)
    {
        $this->container  = $container;
        $this->adapter    = $adapter;
        $this->breadcrumb = $breadcrumb;
        $this->datatables = $datatables;
    }

    /**
     * response for roles list
     * 
     * @return \Illuminate\View\View
     */
    public function table()
    {
        $this->breadcrumb->onInit();
        return $this->datatables->render('antares/acl::roles.index');
    }

    /**
     * View form generator for Antares\Model\Role.
     *
     * @param  \Antares\Model\Role  $model
     *
     * @return \Antares\Contracts\Html\Form\Builder
     */
    public function form(Eloquent $model = null)
    {
        $this->breadcrumb->onRoleCreateOREdit($model);
        return new RoleForm($model);
    }

    /**
     * edit action presenter
     * 
     * @return type
     */
    public function edit(Eloquent $eloquent, array $available = array())
    {
        app('antares.asset')->container('antares/foundation::application')->add('webpack_acl', '/_dist/js/view_acl.js', ['forms_basic']);
        publish('acl', ['js/control.js']);
        $id         = $eloquent->id;
        $instances  = $this->container->make('antares.acl')->all();
        $form       = $this->form($eloquent);
        $modules    = $this->adapter->modules();
        $collection = $this->container->make('antares.memory')->make('collector')->all();
        foreach ($collection as $item) {
            array_push($available, $item['aid']);
        }

        return compact('groups', 'id', 'form');
    }

}
