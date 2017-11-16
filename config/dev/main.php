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
        'apiUrl' => 'http://bb.dev15.com',
        'assetsUrl' => '/static/',
        'uploadPath' => '/vagrant/newboss/Agent/web/upload/',//针对非上传到CDN配置生效
        'visitPath' => '/upload/',//访问配置
    ],
];