<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/27
 * Time: 19:19
 */
return [
    'http' => [
        'admin' => [
            'namespace'  => 'Admin',
            'prefix'     => 'admin',
            'middleware' => 'admin'
        ],
        'web' => [

        ],
        'mobile' => [
            'namespace'  => 'Mobile',
            'prefix'     => 'mobile',
            'middleware' => 'mobile'
        ],
        'weChat' => [

        ],
        'nginx' => [
            'namespace'  => 'Nginx',
            'prefix'     => 'nginx',
        ]
    ],
    'api' => [
        'service' => [
            'namespace' => 'Service',
            'prefix'    => 'service',
            'middleware' => 'api'
        ],
        'oa' => [
            'namespace' => 'OA',
            'prefix'    => 'oa',
            'middleware' => 'api'
        ]
    ]
];