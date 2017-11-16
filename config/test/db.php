<?php

return [
    "db" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.100.30;dbname=baseboss',
        'username' => 'baseboss',
        'password' => 'baseboss123',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_agent" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;port=3306;dbname=agent_IDS',
        'username' => 'zrf',
        'password' => 'zrf@root',
        'charset' => 'utf8mb4',
        'tablePrefix'=>''
    ],
    "db_datacenter" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.100.30;dbname=datacenter',
        'username' => 'datacenter',
        'password' => 'datacenter123',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_webadmin" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.100.30;dbname=webadmin',
        'username' => 'webadmin',
        'password' => 'webadmin@root',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_wsh" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.100.30;port=3306;dbname=testbaseapi',
        'username' => 'testapi',
        'password' => 'testapi@888',
        'charset' => 'utf8mb4',
        'tablePrefix'=>''
    ],
    "db_datacenter_source" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.100.30;dbname=datacenter_source',
        'username' => 'dc_source',
        'password' => 'lfh56XaUzK1',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_datacenter_statistics" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.100.30;dbname=datacenter_statistics',
        'username' => 'dc_source',
        'password' => 'lfh56XaUzK1',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
];

