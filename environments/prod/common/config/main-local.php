<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-uf60e863g9qs2kudv.mysql.rds.aliyuncs.com;dbname=xijin',
            'username' => 'xijin_prod',
            'password' => '!@#Abc123',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'r-uf61wik322lfmzp2h6.redis.rds.aliyuncs.com',
            'password' => '!@#Abc123',
            'port' => 6379,
            'database' => 3,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'r-uf61wik322lfmzp2h6.redis.rds.aliyuncs.com',
                'password' => '!@#Abc123',
                'port' => 6379,
                'database' => 3,
            ],
        ]
    ],
];
