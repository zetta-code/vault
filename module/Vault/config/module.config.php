<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zetta\DoctrineUtil\Controller\Service\ControllerWithEntityManagerFactory;

return [
    'controllers' => [
        'aliases' => [
            'Zetta\Vault\Controller\Accounts' => Controller\AccountsController::class,
            'Zetta\Vault\Controller\Credentials' => Controller\CredentialsController::class,
            'Zetta\Vault\Controller\Index' => Controller\IndexController::class,
            'Zetta\Vault\Controller\Organizations' => Controller\OrganizationsController::class,
            'Zetta\Vault\Controller\Sections' => Controller\SectionsController::class,
        ],
        'factories' => [
            Controller\AccountsController::class => ControllerWithEntityManagerFactory::class,
            Controller\CredentialsController::class => ControllerWithEntityManagerFactory::class,
            Controller\IndexController::class => ControllerWithEntityManagerFactory::class,
            Controller\OrganizationsController::class => ControllerWithEntityManagerFactory::class,
            Controller\SectionsController::class => ControllerWithEntityManagerFactory::class,
        ],
    ],

    'controller_plugins' => [
        'aliases' => [
            'crypt' => Controller\Plugin\Crypt::class,
        ],
        'factories' => [
            Controller\Plugin\Crypt::class => Service\WithCryptServiceFactory::class,
        ]
    ],

    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [dirname(__DIR__) . '/src/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'vault' => [
                'type' => Literal::class,
                'priority' => 10,
                'options' => [
                    'route' => '/vault',
                    'defaults' => [
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action' => 'index'
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'priority' => 5,
                        'options' => [
                            'route' => '/:controller[/[:action[/[:id]]]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => __NAMESPACE__ . '\Controller',
                                'action' => 'index'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\Crypt::class => Service\CryptFactory::class,
        ],
    ],

    'view_helpers' => [
        'aliases' => [
            'crypt' => View\Helper\Crypt::class,
        ],
        'factories' => [
            View\Helper\Crypt::class => Service\WithCryptServiceFactory::class,
        ]
    ],

    'view_manager' => [
        'controller_map' => [
            'Zetta' => true
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];