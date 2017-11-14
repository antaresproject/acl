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

namespace Antares\Acl\Processor;

use Antares\Acl\Http\Presenters\Role as RolePresenter;
use Antares\Contracts\Foundation\Foundation;
use Antares\Contracts\Authorization\Factory;
use Antares\Acl\Contracts\ModulesAdapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Antares\Model\Role as Eloquent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Antares\Model\Action;
use Antares\Support\Str;
use Exception;

class Role extends Processor
{

    /**
     * control scripts config container
     * 
     * @var array
     */
    protected $config;

    /**
     * memory provider
     *
     * @var \Antares\Form\Provider\Provider 
     */
    protected $collector;

    /**
     * ACL instance.
     *
     * @var \Antares\Contracts\Authorization\Factory
     */
    protected $acl;

    /**
     * Setup a new processor instance.
     *
     * @param  \Antares\Acl\Http\Presenters\Role  $presenter
     * @param  \Antares\Contracts\Foundation\Foundation  $foundation
     * @param  \Antares\Contracts\Authorization\Factory  $acl
     */
    public function __construct(RolePresenter $presenter, Foundation $foundation, Factory $acl)
    {
        $this->presenter  = $presenter;
        $this->foundation = $foundation;
        $this->model      = $foundation->make('antares.role');
        $this->acl        = $acl;
    }

    /**
     * view list action
     * 
     * @return \Illuminate\Http\Response|\Illuminate\View\View 
     */
    public function index()
    {
        return $this->presenter->table();
    }

    /**
     * View create a role page.
     *
     * @param  object  $listener
     *
     * @return mixed
     */
    public function create($listener)
    {

        $eloquent = $this->model;
        $form     = $this->presenter->form($eloquent);
        return $listener->createSucceed(compact('eloquent', 'form'));
    }

    /**
     * View edit a role page.
     *
     * @param  object  $listener
     * @param  string|int  $id
     *
     * @return mixed
     */
    public function edit($listener, $id)
    {
        $eloquent = $this->model->findOrFail($id);
        $data     = $this->presenter->edit($eloquent);
        return $listener->editSucceed($data);
    }

    /**
     * Store a role.
     *
     * @param  object  $listener
     * @param  array   $input
     *
     * @return mixed
     */
    public function store($listener, array $input)
    {
        $role = $this->model;
        $form = $this->presenter->form($role);
        if (!$form->isValid()) {
            return $listener->storeValidationFailed($form->getMessageBag());
        }
        try {
            $this->saving($role, $input, 'create');
        } catch (Exception $e) {
            Log::warning($e);
            return $listener->storeFailed(['error' => $e->getMessage()]);
        }
        return $listener->storeSucceed($role);
    }

    /**
     * Update a role.
     *
     * @param  object  $listener
     * @param  array   $input
     * @param  int     $id
     *
     * @return mixed
     */
    public function update($listener, array $input, $id)
    {

        if ((int) $id !== (int) $input['id']) {
            return $listener->userVerificationFailed();
        }
        $role = $this->model->findOrFail($id);
        $form = $this->presenter->form($role);
        if (!$form->isValid()) {
            return $listener->updateValidationFailed($form->getMessageBag(), $id);
        }
        try {
            $this->saving($role, $input, 'update');
        } catch (Exception $e) {
            Log::warning($e);
            return $listener->updateFailed(['error' => $e->getMessage()]);
        }

        return $listener->updateSucceed();
    }

    /**
     * Delete a role.
     *
     * @param  object  $listener
     * @param  string|int  $id
     *
     * @return mixed
     */
    public function destroy($listener, $id)
    {
        $role = $this->model->findOrFail($id);
        try {
            if ($role->users->count() > 0) {
                throw new Exception('Unable to delete group with assigned users.');
            }
            DB::transaction(function () use ($role) {
                $role->delete();
            });
        } catch (Exception $e) {
            Log::warning($e);
            return $listener->destroyFailed(['error' => $e->getMessage()]);
        }

        return $listener->destroySucceed($role);
    }

    /**
     * Save the role.
     *
     * @param  \Antares\Model\Role  $role
     * @param  array  $input
     * @param  string  $type
     *
     * @return bool
     */
    protected function saving(Eloquent $role, $input = [], $type = 'create')
    {
        $beforeEvent = ($type === 'create' ? 'creating' : 'updating');
        $afterEvent  = ($type === 'create' ? 'created' : 'updated');
        $name        = $input['name'];
        $role->fill([
            'name'        => snake_case($name, '-'),
            'full_name'   => $name,
            'area'        => config('areas.default'),
            'description' => $input['description']
        ]);
        if (!$role->exists && isset($input['roles'])) {
            $role->parent_id = $input['roles'];
        }
        $this->fireEvent($beforeEvent, [$role]);
        $this->fireEvent('saving', [$role]);

        DB::transaction(function() use($role, $input) {
            if (!is_null(input('acl'))) {
                $all = $this->acl->all();
                if (empty($all)) {
                    throw new Exception('Acl verification failed.');
                }
                $roleId = key(input('acl'));

                $role = $role->newQuery()->where('id', $roleId)->firstOrFail();

                $roleName = $role->name;
                $allowed  = array_keys(input('acl')[$roleId]);

                foreach ($all as $component => $details) {
                    $acl     = $this->acl->get($component);
                    $actions = $details->actions->get();
                    foreach ($actions as $actionId => $name) {
                        $allow = in_array($actionId, $allowed);
                        $acl->allow($roleName, $name, $allow);
                    }
                    $acl->save();
                }
            }

            $role->save();
            $this->import($input, $role);
        });



        $this->fireEvent($afterEvent, [$role]);
        $this->fireEvent('saved', [$role]);

        return true;
    }

    /**
     * import permissions when copy
     * 
     * @param array $input
     * @param Model $role
     */
    protected function import(array $input, Model $role)
    {
        if (isset($input['import']) && !is_null($from = $input['roles'])) {
            $permission = $this->foundation->make('antares.auth.permission');

            $permissions = $permission->where('role_id', $from)->get();

            $permissions->each(function(Model $model) use($permission, $role) {
                $attributes = $model->getAttributes();
                $insert     = array_except($attributes, ['id', 'role_id']) + ['role_id' => $role->id];
                $permission->newInstance($insert)->save();
            });
        }
        return true;
    }

    /**
     * Fire Event related to eloquent process.
     *
     * @param  string  $type
     * @param  array   $parameters
     *
     * @return void
     */
    protected function fireEvent($type, array $parameters = [])
    {
        Event::fire("antares.control.{$type}: roles", $parameters);
    }

    /**
     * Modules structure as tree
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function tree($id)
    {
        $eloquent  = $this->model->findOrFail($id);
        $instances = app('antares.acl')->all();
        $return    = [
            'name'    => 'Root',
            'open'    => true,
            'checked' => false,
        ];
        $modules   = app(ModulesAdapter::class)->modules();
        foreach ($modules as $module) {
            $item        = [
                'saveName'      => array_get($module, 'full_name', '---'),
                'name'          => array_get($module, 'full_name', '---'),
                'indeterminate' => false,
                'checked'       => false,
                'open'          => true,
            ];
            if (!is_null($description = array_get($module, 'description'))) {
                array_set($item, 'description', $description);
            }
            $actions = array_get($module, 'actions', []);
            if (empty($actions) or ! isset($instances[$module['namespace']])) {
                continue;
            }
            $children = [];
            $checked  = true;
            $keys     = array_keys($actions);

            $actionsWithoutCategories = Action::query()->whereIn('id', $keys)->whereNull('category_id')->get();

            foreach ($actionsWithoutCategories as $action) {
                $checked    = $instances[$module['namespace']]->check($eloquent->name, $action->name);
                $key        = array_search($action->name, $actions);
                $children[] = [
                    'saveName'      => "acl[{$id}][{$key}]",
                    'name'          => Str::humanize($action->name),
                    'indeterminate' => false,
                    'checked'       => $checked,
                    'value'         => $action->name,
                    'description'   => $action->description
                ];
                if (!$checked) {
                    $checked = false;
                }
            }
            $actionsWithCategories = \Antares\Model\ActionCategories::query()->with(['actions' => function($query) use($keys) {
                            $query->whereIn('id', $keys);
                        }])->get();

            foreach ($actionsWithCategories as $category) {
                if ($category->actions->isEmpty()) {
                    continue;
                }
                $subchildren = [];
                foreach ($category->actions as $action) {
                    $checked       = $instances[$module['namespace']]->check($eloquent->name, $action->name);
                    $key           = array_search($action->name, $actions);
                    $subchildren[] = [
                        'saveName'      => "acl[{$id}][{$key}]",
                        'name'          => Str::humanize($action->name),
                        'indeterminate' => false,
                        'checked'       => $checked,
                        'value'         => $action->name,
                        'description'   => $action->description
                    ];
                }
                $children[] = [
                    'name'          => $category->name,
                    'saveName'      => "acl_category_" . $category->id,
                    'indeterminate' => false,
                    'checked'       => true,
                    'children'      => $subchildren,
                    'open'          => true
                ];
            }

            $item['checked'] = $checked;

            $item['children']     = $children;
            $return['children'][] = $item;
        }
        return new JsonResponse($return);
    }

}
