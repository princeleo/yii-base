<?php

return [
    "db" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;dbname=baseboss',
        'username' => 'zrf',
        'password' => 'zrf@root',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_agent" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;port=3306;dbname=agent_IDS',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'tablePrefix'=>''
    ],
    "db_datacenter" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;dbname=datacenter',
        'username' => 'root',
        'password' => '26@root',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_webadmin" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;dbname=webadmin',
        'username' => 'zrf',
        'password' => 'zrf@root',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_wsh" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.15;port=3306;dbname=newwsh',
        'username' => 'newwsh',
        'password' => 'newwsh@dev.com',
        'charset' => 'utf8mb4',
        'tablePrefix'=>''
    ],
    "db_datacenter_source" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;dbname=datacenter_source',
        'username' => 'zrf',
        'password' => 'zrf@root',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
    "db_datacenter_statistics" => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.100.200.26;dbname=datacenter_statistics',
        'username' => 'zrf',
        'password' => 'zrf@root',
        'charset' => 'utf8',
        'tablePrefix'=>''
    ],
];
