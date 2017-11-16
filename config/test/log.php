<?php
/**
 * Author: Richard <chenz@snsshop.cn>
 * Date: 2016/11/25
 * Time: 14:41
 */

$sgl_log = require "sgl_log.php";

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        array_merge(
            [
                'class' => 'app\common\log\SglTarget',
            ],
            $sgl_log
        ),
    ],
];