<?php
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
require(__DIR__ . '/../config/' . YII_ENV . '/define.php');

$components  = [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'log' => include __DIR__ . '/' . YII_ENV . '/log.php',
];


return [
    'id' => 'script',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'gii'],
    'controllerNamespace' => 'app\commands',
    'modules' => [
        'gii' => 'yii\gii\Module',
        'script' => 'app\modules\script\script',//API模块
        'base-boss' => 'app\modules\script\base-boss',//API模块
        //'script' => 'app\modules\script\script',//API模块
        //'script' => 'app\modules\script\script',//API模块
    ],
    'components' => array_merge($components, require(__DIR__ . '/' . YII_ENV . '/db.php')),
];
