<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/6/1
 * Time: 10:53
 */
return [
    // 是否使用云存储，值为空则为不使用，目前支持的云存储：aliyun
    'oss' => false,
    'aliyun' => [
        'curlDomain' => 'http://laravel.stargranzon.net/api/service/oss/upload-file',
        'imageDomain' => 'http://star-granzon.oss-cn-hangzhou.aliyuncs.com'
    ],
    'qiniu' => [
//        'curlDomain' => 'http://laravel.stargranzon.net/api/service/oss/upload-file',
//        'imageDomain' => 'http://star-granzon.oss-cn-hangzhou.aliyuncs.com'
    ]
];