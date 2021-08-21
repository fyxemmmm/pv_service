<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$rules = require __DIR__ . '/rules.php';

return [
    'id' => 'app-service',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'service\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-service',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'service\models\Admin',
            'enableSession' => false,
            'loginUrl' => null,
            'enableAutoLogin' => true,
            // 'identityCookie' => ['name' => '_identity-service_backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the service
            'name' => 'advanced-service',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'common/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => $rules,
        ],
    ],
    'params' => $params,
    
];
