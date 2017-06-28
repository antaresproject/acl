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
use Antares\Acl\Contracts\Command\Synchronizer as SynchronizerContract;
use Antares\Acl\Contracts\ControlsAdapter as ControlsAdapterContract;
use Antares\Acl\Contracts\ModulesAdapter as ModulesAdapterContract;
use Antares\Acl\Adapter\ControlsAdapter;
use Antares\Acl\Adapter\ModulesAdapter;
use Antares\Acl\Command\Synchronizer;

return [
    'allow_register_with_other_roles' => true,
    'di'                              => [
        SynchronizerContract::class    => Synchronizer::class,
        ControlsAdapterContract::class => ControlsAdapter::class,
        ModulesAdapterContract::class  => ModulesAdapter::class
    ],
    'localtime'                       => [
        'enable' => false,
    ],
    'memory'                          => [
        'default' => [
            'model' => 'Antares\Acl\Model\Middleware',
            'cache' => false,
            'crypt' => true
        ]
    ]
];
