<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\Id;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'datetime_functions' => [],
                'numeric_functions' => [],
                'string_functions'   => [],
                'types' => [
                    'date' => Zetta\DoctrineUtil\Types\JenssegersDateType::class,
                    'datetime' => Zetta\DoctrineUtil\Types\JenssegersDateTimeType::class,
                    'datetimetz' => Zetta\DoctrineUtil\Types\JenssegersDateTimeTzType::class,
                    'time' => Zetta\DoctrineUtil\Types\JenssegersTimeType::class
                ]
            ]
        ]
    ],
    'mail' => [
        'options' => [
            'sender' => 'sendmail',
            'from-email' => 'zetta@code',
            'from-name' => 'Zetta',
        ],
        'transport' => [
            'options' => [
                'name' => 'localhost',
                'host' => 'localhost',
                'port' => '465',
                'connection_class' => 'login',
                'connection_config' => [
                    'username' => 'zetta',
                    'password' => 'code',
                    'ssl' => 'ssl'
                ],
            ],
        ],
    ],
    // Session configuration.
    'session_config' => [
        'name' => 'ZF3',
        // Session cookie will expire in 1 hour.
        'cookie_lifetime' => 60*60*1,
        // Session data will be stored on server maximum for 30 days.
        'gc_maxlifetime'  => 60*60*24*30,
        // Session path.
        'save_path'       => 'data/sessions',
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            Id::class
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
    // Zetta Bootstrap
    'zend_boostrap' => [
        'thumbnail' => [
            'defaultPath' => './public/img/thumb-boy.svg',
            'girlPath' => './public/img/thumb-girl.svg',
            'width' => 128,
            'height' => 128
        ],
    ]
];
