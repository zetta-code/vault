<?php

use Application\Entity\Role;

return [
    'zend_authentication' => [
        'layout' => 'zetta/zend-authentication/layout/default',
        'templates' => [
            'password-recover' => 'zetta/zend-authentication/password-recover',
            'recover' => 'zetta/zend-authentication/recover',
            'signin' => 'zetta/zend-authentication/signin',
            'signup' => 'zetta/zend-authentication/signup',
        ],
        'routes' => [
            'home' => [
                'name' => 'home',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'redirect' => [
                'name' => 'home',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'authenticate' => [
                'name' => 'authentication/authenticate',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'confirm-email' => [
                'name' => 'authentication/confirm-email',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'password-recover' => [
                'name' => 'authentication/password-recover',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'recover' => [
                'name' => 'authentication/recover',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'signin' => [
                'name' => 'authentication/signin',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'signout' => [
                'name' => 'authentication/signout',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'signup' => [
                'name' => 'authentication/signup',
                'params' => [],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'account' => [
                'name' => 'authentication/default',
                'params' => ['controller' => 'account'],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
            'password-change' => [
                'name' => 'authentication/default',
                'params' => ['controller' => 'account', 'action' => 'password-change'],
                'options' => [],
                'reuseMatchedParams' => false,
            ],
        ],
        'options' => [
            'identityClass' => Application\Entity\User::class,
            'credentialClass' => Application\Entity\Credential::class,
            'roleClass' => Role::class,
            'identityProperty' => 'username',
            'emailProperty' => 'email',
            'credentialProperty' => 'value',
            'credentialIdentityProperty' => 'user',
            'credentialTypeProperty' => 'type',
            'credentialType' => Application\Entity\Credential::TYPE_PASSWORD,
            'credentialCallable' => 'Application\Entity\Credential::check',
        ],
        'default' => [
            'signAllowed' => false,
            'role' => Role::ID_MEMBER,
        ],
        'acl' => [
            'defaultRole' => Role::ID_GUEST,
            'roles' => [
                Role::ID_GUEST => null,
                Role::ID_MEMBER => [Role::ID_GUEST],
                Role::ID_INTERN => [Role::ID_MEMBER],
                Role::ID_EDITOR => [Role::ID_INTERN],
                Role::ID_MANAGER => [Role::ID_EDITOR],
                Role::ID_SUPER_MANAGER => [Role::ID_MANAGER],
                Role::ID_ADMIN => [Role::ID_SUPER_MANAGER],
            ],
            'resources' => [
                'allow' => [
                    'Application\Controller\Index' => [
                        '' => [Role::ID_MEMBER],
                    ],
                    'Application\Controller\Users' => [
                        '' => [Role::ID_SUPER_MANAGER],
                    ],
                    'Zetta\Vault\Controller\Accounts' => [
                        '' => [Role::ID_MANAGER],
                    ],
                    'Zetta\Vault\Controller\Credentials' => [
                        '' => [Role::ID_MANAGER],
                        'reveal' => [Role::ID_INTERN],
                        'index' => [Role::ID_EDITOR ],
                        'edit' => [Role::ID_EDITOR ],
                    ],
                    'Zetta\Vault\Controller\Index' => [
                        '' => [Role::ID_INTERN],
                    ],
                    'Zetta\Vault\Controller\Sections' => [
                        '' => [Role::ID_SUPER_MANAGER],
                    ],
                    'Zetta\ZendAuthentication\Controller\Account' => [
                        '' => [Role::ID_MEMBER],
                    ],
                    'Zetta\ZendAuthentication\Controller\Auth' => [
                        'authenticate' => [Role::ID_GUEST],
                        'confirm-email' => [Role::ID_GUEST],
                        'password-recover' => [Role::ID_GUEST],
                        'recover' => [Role::ID_GUEST],
                        'signin' => [Role::ID_GUEST],
                        'signout' => [Role::ID_GUEST],
//                        'signup' => [Role::ID_GUEST],
                    ],
                    'Zetta\ZendAuthentication\Menu' => [
                        'account' => [Role::ID_MEMBER],
                    ]
                ],
                'deny' => [
                    'Zetta\ZendAuthentication\Controller\Auth' => [
                        'signup' => [Role::ID_MEMBER],
                    ],
                    'Application\Controller\Users' => [
                        'add' => [Role::ID_EDITOR],
                    ],
                ],
            ],
        ],
    ],
];
