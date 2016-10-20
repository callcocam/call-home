<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Home;

return [
    'router' => [
        'routes' => [
            "home-site" => [
                "type" => "Literal",
                "options" => [
                    "route" => "/",
                    "defaults" => [
                        "__NAMESPACE__" => "Home\Controller",
                        "controller" => "Home",
                        "action" => "index",
                    ],
                ],
            ],
            "notification" => [
                "type" => "Literal",
                "options" => [
                    "route" => "/notification",
                    "defaults" => [
                        "__NAMESPACE__" => "Home\Controller",
                        "controller" => "Pg",
                        "action" => "notification",
                    ],
                ],
            ],

            "home" => [
                "type" => "Literal",
                "options" => [
                    "route" => "/home",
                    "defaults" => [
                        "__NAMESPACE__" => "Home\Controller",
                        "controller" => "Home",
                        "action" => "index",
                    ],
                ],
                "may_terminate" => true,
                "child_routes" => [
                    "default" => [
                        "type" => "Segment",
                        "options" => [
                            "route" => "/[:controller[/:action][/:id]]",
                            "constraints" => [
                                "controller" => "[a-zA-Z][a-zA-Z0-9_-]*",
                                "action" => "[a-zA-Z][a-zA-Z0-9_-]*",
                            ],
                            "defaults" => [
                            ],
                        ],
                    ],
                ],
            ],

        ],
    ],
    'controllers' => [
        'factories' => [
            'Home\Controller\Home' => 'Home\Controller\Factory\HomeControllerFactory',
            'Home\Controller\Pg' => 'Home\Controller\Factory\PgControllerFactory',
        ],
    ],
    'view_manager' => [
       'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
