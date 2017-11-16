<?php

return [
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '10.100.100.35',
            'port' => 6381,
            'database' => 0,
        ],
    ],
    'bootstrap' => [
        'debug',
        'gii'
    ],
    'modules' => [
        'debug' => 'yii\debug\Module',
        'gii' => 'yii\gii\Module'
    ],
    'params' => [
        'exteriorUrl'=>'http://www.devbaseboss.com:81',//外部访问地址
        'visitUrl'=>'http://www.devbaseboss.com',
        'apiUrl' => 'http://bossdev.cc',
        'assetsUrl' => '/static/',
        'speed_pos_conf' => [ //进件配置
            'url' => 'http://betadataapi.speedpos.snsshop.net/thirdapi/',
            'key' => 'a9794fb8329ffe8700680c76737c4153',
            'channel' => '100001',
            'code' => '23743731',
        ],
        'uploadPath' => '/vagrant/newboss/BaseBoss/web/upload/',//针对非上传到CDN配置生效
        'visitPath' => '/upload/',//访问配置
        'app_xpush_conf' => [ //信鸽推送配置
            'access_id' => 2100252838,
            'secret_key' => '4dbcc4f5fe1ebbfafe9894dc2fae7921',
            'account_prefix' => 'dt_dev_',
        ],
        'app_jpush_conf' => [//极光推送配置
            'app_key' => '5341960f90a5d8b785889eb4',
            'secret_key' => '3b581f4dfe2254771193e685',
            'account_prefix' => 'dt_dev_',
        ],
        "SwiftPass" => [    //威富通配置
            "mch_id" => "7551000001",
            "merchant_key" => "9d101c97133837e13dde2d32a5054abb"
        ],
    ],
];