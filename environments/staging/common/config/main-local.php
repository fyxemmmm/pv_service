<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=47.103.61.179;dbname=xijin',
            'username' => 'root',
            'password' => '!@#Abc123',
            'charset' => 'utf8',
        ],
		'redis' => [
			'class' => 'yii\redis\Connection',
			'hostname' => 'localhost',
			'port' => 6379,
			'database' => 1,
		],
		'cache' => [
			'class' => 'yii\redis\Cache',
			'redis' => [
				'hostname' => 'localhost',
				'port' => 6379,
				'database' => 1,
			],
		],
    ],
];
