<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'controller' => [
        'enableCsrfValidation' => false,
    ],
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'sourceLanguage' => 'zh-CN',
    'defaultRoute' => 'index/index'
];
