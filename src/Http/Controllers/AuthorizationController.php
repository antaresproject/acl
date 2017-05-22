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

namespace Antares\Acl\Http\Controllers;

use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Input;
use Antares\Acl\Processor\Authorization;
use Antares\Acl\Contracts\Command\Synchronizer;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Config\Repository;
use Illuminate\Support\Facades\Response;

class AuthorizationController extends AdminController
{

    /**
     * The synchronizer implementation.
     *
     * @var \Antares\Acl\Contracts\Command\Synchronizer
     */
    protected $synchronizer;

    /**
     * Setup a new controller.
     *
     * @param  \Antares\Acl\Processor\Authorization  $processor
     * @param  \Antares\Acl\Contracts\Command\Synchronizer  $synchronizer
     */
    public function __construct(Authorization $processor, Synchronizer $synchronizer)
    {
        $this->processor    = $processor;
        $this->synchronizer = $synchronizer;
        parent::__construct();
    }

    /**
     * Define the middleware.
     *
     * @return void
     */
    protected function setupMiddleware()
    {
        $this->middleware('antares.manage');
        $this->middleware('antares.csrf', ['only' => 'sync']);
    }

    /**
     * Get default resources landing page.
     *
     * @return mixed
     */
    public function edit()
    {
        return $this->processor->edit($this, Input::get('name', 'antares'));
    }

    /**
     * Update ACL metric.
     *
     * @return mixed
     */
    public function update()
    {
        return $this->processor->update($this, Input::all());
    }

    /**
     * Get sync roles action.
     *
     * @param  string  $vendor
     * @param  string|null  $package
     *
     * @return mixed
     */
    public function sync($vendor, $package = null)
    {
        return $this->processor->sync($this, $vendor, $package);
    }

    /**
     * Response when lists ACL page succeed.
     * 
     * @param array $data
     * @param Repository $config
     * @return \Illuminate\View\View
     */
    public function indexSucceed(array $data)
    {
        return view('antares/acl::acl.index', $data);
    }

    /**
     * Response when ACL is updated.
     *
     * @return mixed
     */
    public function updateSucceed()
    {
        $this->synchronizer->handle();
        app('antares.memory')->make('component.default')->update();
        $message = trans('antares/acl::response.acls.update');
        return (app('request')->ajax()) ? Response::json(['message' => $message], 200) : $this->redirectWithMessage(handles("antares::acl/index/roles"), $message);
    }

    /**
     * Response when sync roles succeed.
     *
     * @param  \Illuminate\Support\Fluent   $acl
     *
     * @return mixed
     */
    public function syncSucceed(Fluent $acl)
    {
        $message = trans('antares/acl::response.acls.sync-roles', [
            'name' => $acl->get('name'),
        ]);

        return $this->redirectWithMessage(handles("antares::acl/acl?name={$acl->get('name')}"), $message);
    }

    /**
     * Response when acl verification failed.
     *
     * @return mixed
     */
    public function aclVerificationFailed()
    {
        return $this->suspend(404);
    }

}
