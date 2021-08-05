<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Zend\Mvc\I18n\Translator;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zetta\DoctrineUtil\Controller\Service\ControllerWithEntityManagerFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Zetta\Vault\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => ':controller[/[:action[/[:id]]]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                                'extension' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Application\Controller',
                                'action' => 'index',
                            ]
                        ],
                        'priority' => 6
                    ],
                ]
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'aliases' => [
            'Application\Controller\Index' => Controller\IndexController::class,
            'Application\Controller\Users' => Controller\UsersController::class,
        ],
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\UsersController::class => ControllerWithEntityManagerFactory::class,
        ],
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
    'service_manager' => [
        'factories' => [
            'mail.transport' => Factory\TransportFactory::class,
        ],
        'delegators' => [
            Translator::class => [
                Delegator\TranslatorDelegator::class,
            ],
        ],
    ],
    'translator' => [
        'locale' => 'pt_BR',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../../../data/language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => false,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/default/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/layout'            => __DIR__ . '/../view/error/layout.phtml',
            'error/403'               => __DIR__ . '/../view/error/403.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
