<?php
/**
 * @link      http://github.com/zetta-code/zend-skeleton-application for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

return [
    'navigation' => [
        'default' => [
            [
                'type' => \Zetta\ZendBootstrap\Navigation\Page\Avatar::class,
                'label' => '<b class="caret"></b>',
                'uri' => '#',
                'liClass' => 'user-nav',
                'resource' => 'Zetta\ZendAuthentication\Menu',
                'privilege' => 'account',
                'pages' => [
                    [
                        'type' => \Zetta\ZendBootstrap\Navigation\Page\Avatar::class,
                        'no-image' => true,
                        'label' => '<b class="caret"></b>',
                        'uri' => '',
                        'liClass' => 'user-info',
                        'resource' => 'Zetta\ZendAuthentication\Menu',
                        'privilege' => 'account',
                    ],
                    [
                        'label' => _('Profile'),
                        'class' => 'dropdown-item',
                        'addon-left' => '<i class="fa fa-user fa-fw"></i> ',
                        'route' => 'authentication/default',
                        'controller' => 'account',
                        'resource' => 'Zetta\ZendAuthentication\Controller\Account',
                        'privilege' => 'index',
                    ],
                    [
                        'label' => _('Sign out'),
                        'class' => 'dropdown-item',
                        'addon-left' => '<i class="fa fa-sign-out fa-fw"></i> ',
                        'route' => 'authentication/signout',
                        'resource' => 'Zetta\ZendAuthentication\Controller\Auth',
                        'privilege' => 'signout',
                    ],
                ],
            ],
        ],
        'Sidebar' => [
            [
                'label' => _('Home'),
                'addon-left' => '<i class="fa fa-fw fa-lg fa-home mr-3 text-primary"></i> ',
                'class' => 'nav-link',
                'route' => 'home',
                'resource' => 'Zetta\Vault\Controller\Index',
                'privilege' => 'index',
            ],
            [
                'label' => _('Credentials'),
                'addon-left' => '<i class="fa fa-fw fa-lg fa-lock mr-3 text-warning"></i> ',
                'class' => 'nav-link',
                'route' => 'vault/default',
                'controller' => 'credentials',
                'resource' => 'Zetta\Vault\Controller\Credentials',
                'privilege' => 'index',
            ],
            [
                'label' => _('Accounts'),
                'addon-left' => '<i class="fa fa-fw fa-lg fa-bookmark mr-3 text-info"></i> ',
                'class' => 'nav-link',
                'route' => 'vault/default',
                'controller' => 'accounts',
                'resource' => 'Zetta\Vault\Controller\Accounts',
                'privilege' => 'index',
            ],

            [
                'label' => _('Sections'),
                'addon-left' => '<i class="fa fa-fw fa-lg fa-puzzle-piece  mr-3 text-success"></i> ',
                'class' => 'nav-link',
                'route' => 'vault/default',
                'controller' => 'sections',
                'resource' => 'Zetta\Vault\Controller\Sections',
                'privilege' => 'index',
            ],
            [
                'label' => _('Users'),
                'addon-left' => '<i class="fa fa-fw fa-lg fa-users mr-3"></i> ',
                'class' => 'nav-link',
                'route' => 'home/default',
                'controller' => 'users',
                'resource' => 'Application\Controller\Users',
                'privilege' => 'index',
            ],
        ],
        'Breadcrumbs' => [
            [
                'label' => _('Home'),
                'route' => 'home',
                'pages' => [
                    [
                        'label' => _('Profile'),
                        'route' => 'authentication/default',
                        'controller' => 'account',
                    ],
                    [
                        'label' => _('Users'),
                        'route' => 'home/default',
                        'controller' => 'users',
                    ],
                ]
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ],
    ],
];
