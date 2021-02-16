<?php
/**
 * module.config.php - Weos Config
 *
 * Main Config File for Weos Module
 *
 * @category Config
 * @package Weos
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

namespace OnePlace\Weos;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    # Weos Module - Routes
    'router' => [
        'routes' => [
            # Web Home
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'home',
                    ],
                ],
            ],
            # Module Basic Route
            'weos-zip' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/somerandom-seo-[:zip]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'list',
                    ],
                ],
            ],
            # Module Basic Route
            'weos-single' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/random-singleseo-[:contact]-[:zip]',
                    'constraints' => [
                        'contact'     => '[0-9]+',
                        'zip'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'view',
                    ],
                ],
            ],
            # Module Basic Route
            'weos' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/app[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'start',
                    ],
                ],
            ],
            'weos-order' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/app/order[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'start',
                    ],
                ],
            ],
            'weos-web' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/web[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\WebController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'weos-booking-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/app/calendar[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'dates',
                    ],
                ],
            ],
            'weos-supplier-api' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/app/supplier[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'list',
                    ],
                ],
            ],
            'weos-calendar-plugin' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/timeslots[/:id]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'timeslots',
                    ],
                ],
            ],
            'weos-bookings' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/booking[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\BookingController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'weos-user' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/app/user[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action'     => 'start',
                    ],
                ],
            ],
            'weos-setup' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/weos/setup[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\InstallController::class,
                        'action'     => 'checkdb',
                    ],
                ],
            ],
        ],
    ],

    # View Settings
    'view_manager' => [
        'template_path_stack' => [
            'weos' => __DIR__ . '/../view',
        ],
    ],

    # Translator
    'translator' => [
        'locale' => 'de_DE',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
];