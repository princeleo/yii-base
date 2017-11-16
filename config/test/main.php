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
        'exteriorUrl'=>'http://testboss4-base.snsshop.net:81',//外部访问地址
        'visitUrl'=>'http://testboss4-base.snsshop.net',
        'apiUrl' => 'http://bossdev.cc',
        'speed_pos_conf' => [ //进件配置
            'url' => 'http://betadataapi.speedpos.snsshop.net/thirdapi/',
            'key' => 'a9794fb8329ffe8700680c76737c4153',
            'channel' => '100001',
            'code' => '23743731',
        ],
        'assetsUrl' => '/static/',
        'uploadPath' => '/data/www/betaboss4/BaseBoss/web/upload/',//针对非上传到CDN配置生效
        'visitPath' => '/upload/',//访问配置
        'app_xpush_conf' => [ //信鸽推送配置
            'access_id' => 2100252838,
            'secret_key' => '4dbcc4f5fe1ebbfafe9894dc2fae7921',
            'account_prefix' => 'dt_beta_',
        ],
        'app_jpush_conf' => [//极光推送配置
            'app_key' => '5341960f90a5d8b785889eb4',
            'secret_key' => '3b581f4dfe2254771193e685',
            'account_prefix' => 'dt_beta_',
        ],
        'shop_app_jpush_config' => [ //商户APP极光推送配置
            'app_key' => 'bc76957013fdcd2abeb2414a',
            'secret_key' => '53b3c04fb2af485e30a7657c',
        ],
        "SwiftPass" => [    //威富通配置
            "mch_id" => "102512469072",
            "merchant_key" => "f281b62fa12d39f7ed596acb9c0a7958"
        ],
    ],
];