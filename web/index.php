<?php

// comment out the following two lines when deployed to production

require(__DIR__ . '/../config/env.php');
require(__DIR__ . '/../config/' . YII_ENV . '/define.php');
require(__DIR__ . '/../common/bootstrap.php');
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/' . YII_ENV . '/main.php')
);
(new yii\web\Application($config))->run();
