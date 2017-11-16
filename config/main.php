<?php

$components = [
    'request' => [
    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    'cookieValidationKey' => 'EsglIcnk_GfVGW_YNDE552KLaPHTWM7m',
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'urlManager'=>[
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        //路由管理
        'rules' => [
            "<module:\w+>/<controller:\w+>/<action:\w+>/<id:\d+>"=>"<module>/<controller>/<action>",
            "<controller:\w+>/<action:\w+>/<id:\d+>"=>"<controller>/<action>",
            "<controller:\w+>/<action:\w+>"=>"<controller>/<action>",
        ],
    ],
    'errorHandler' => [
        'errorAction' => 'public/error',//默认错误处理页
    ],
    'log' => include __DIR__ . '/' . YII_ENV . '/log.php',
];

$config = [
    'id' => 'open-api',
    'language'=>'zh-CN',
    'timeZone'=>'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\controllers',
    'defaultRoute'=>'index/index',//默认路由，控制器+方法
    'modules' => [
        'tools' => 'app\modules\tools\module',//tools模块
        'script' => 'app\modules\script\script',//script模块
        'gate-way' => 'app\modules\gateway\gateway',//对外网关(非BOSS子系统）
        'admin' => 'app\modules\admin\module',//admin
    ],
    'components' => array_merge($components, require(__DIR__ . '/' . YII_ENV . '/db.php')),
    'params' => require(__DIR__ . '/params.php'),
];



return $config;
