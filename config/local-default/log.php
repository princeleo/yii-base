<?php
/**
 * Author: Richard <chenz@snsshop.cn>
 * Date: 2016/11/25
 * Time: 14:41
 */

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error'],
            'logFile' => '@app/runtime/logs/error.log',
            'maxFileSize' => 1024 * 10,
            'maxLogFiles' => 500,
            'rotateByCopy' => false
        ],
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['warning'],
            'logFile' => '@app/runtime/logs/warning.log',
            'maxFileSize' => 1024 * 10,
            'maxLogFiles' => 500,
            'rotateByCopy' => false
        ],
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['info'],
            'logFile' => '@app/runtime/logs/info.log',
            'maxFileSize' => 1024 * 10,
            'maxLogFiles' => 500,
            'rotateByCopy' => false
        ],
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['trace'],
            'logFile' => '@app/runtime/logs/trace.log',
            'maxFileSize' => 1024 * 10,
            'maxLogFiles' => 500,
            'rotateByCopy' => false
        ]
    ],
];