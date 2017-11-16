<?php

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        array_merge(
            [
                'class' => 'app\common\log\SglTarget',
                'logVars' => [],//配置成一个空数组来完全禁止上下文信息包含。或者假如你想要实现你自己提供上下文信息的方式， 你可以重写 yii\log\Target::getContextMessage() 方法。
            ],
            [
                'host' => '10.104.7.217',
                'port' => 13454,
                'appid' => 172,
                'timeout' => 1,
            ]
        ),
    ],
];