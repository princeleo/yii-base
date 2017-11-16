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
        'gii' =>['class' => 'yii\gii\Module', 'allowedIPs' => ['*', '::1']]
    ],
    'params' => [
        'apiUrl' => 'http://bossdev.cc',
        'zhctApiUrl' => 'http://testsnszhct.c.snsshop.net',  //智慧餐厅调用接口url
        'ggtApiUrl' => 'http://betaggt.snsshop.net',        //消费流水调用接口url
        'assetsUrl' => '/static/',
    ],
];