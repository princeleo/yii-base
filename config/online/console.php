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
        'gii' =>['class' => 'yii\gii\Module', 'allowedIPs' => ['*', '::1']]
    ],
    'params' => [
        'apiUrl' => 'http://newboss.vikduo.com',
        'zhctApiUrl' => 'http://snszhct.c.vikduo.com',  //智慧餐厅调用接口url
        'ggtApiUrl' => 'http://ggt.vikduo.com',        //消费流水调用接口url
        'assetsUrl' => '/static/',
    ],
];