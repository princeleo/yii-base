<?php

return [
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '10.104.223.48',
            'port' => 9876,
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
        'exteriorUrl'=>'http://newboss.vikduo.com:81',//外部访问地址
        'visitUrl'=>'http://newboss.vikduo.com',
        'apiUrl' => 'http://newboss.vikduo.com',
        'zhctApiUrl' => 'http://snszhct.c.vikduo.com',  //智慧餐厅调用接口url
        'ggtApiUrl' => 'http://ggt.vikduo.com',        //消费流水调用接口url
        'assetsUrl' => '/static/',
        'speed_pos_conf' => [ //进件配置
            'url' => 'https://dataapi.speedpos.cn/thirdapi/',
            'key' => 'zbms1v2f9oxj7y3o7x4luwkknrc45qyw',
            'channel' => '100047',
            'code' => '23743731',
        ],
        'uploadPath' => '/data/www/newboss/BaseBoss/web/upload/',//针对非上传到CDN配置生效
        'visitPath' => '/upload/',//访问配置
        'app_xpush_conf' => [ //信鸽推送配置
            'access_id' => 2100252838,
            'secret_key' => '4dbcc4f5fe1ebbfafe9894dc2fae7921',
            'account_prefix' => 'dt_ol_',
        ],
        'app_jpush_conf' => [//极光推送配置
            'app_key' => '5341960f90a5d8b785889eb4',
            'secret_key' => '7445d569a7ffb67dbbd61b46',
            'account_prefix' => 'dt_ol_',
        ],
        'shop_app_jpush_config' => [ //商户APP极光推送配置
            'app_key' => 'bc76957013fdcd2abeb2414a',
            'secret_key' => 'd76c973df6e375eb0eb94a5b',
        ],
        "SwiftPass" => [    //威富通配置
            "mch_id" => "102512469072",
            "merchant_key" => "f281b62fa12d39f7ed596acb9c0a7958"
        ],
    ],
];