<?php
/**
 * Created by PhpStorm.
 * Author Sojo
 * Date: 2016/3/20
 * Time: 17:47
 */
return [
    // 密钥
    'appKey' => '23386722',
    'appSecret' => '1ebd5151fcbef795ca1402d07f099c92',

    // 短信服务
    'sms' => [
        // 自定义场景，以对应短信模板、签名
        'scenarios' => [
            // 注册验证
            'register' => [
                'signName' => '注册验证',
                'templateCode' => 'SMS_10640904',
            ]
        ],
    ]
];