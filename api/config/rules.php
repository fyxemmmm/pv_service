<?php
return [
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['product', 'article', 'comment', 'focu', 'user-report', 'user-blacklist', 'article-report', 'article-type', 'article-type-subscription', 'goods', 'goods-order', 'coupon', 'hello']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['work']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['message-dynamic']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['message-setting']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['activity', 'activity-item-sign-up']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['app-setting']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['make-money-group']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['make-money-type']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['make-money-group-user']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['make-money-banner']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user'],
        'extraPatterns' => [
            'GET my-collect' => 'my-collect',
            'GET my-browse-record' => 'my-browse-record',
            'GET info' =>'info',
            'GET check-bind' => 'check-bind',
            'PUT update' => 'update'
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-profile-browse'],
        'extraPatterns' => [
            'GET today-browse' => 'today-browse',
            'GET total-browse' => 'total-browse'
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'general',
        'except' => ['index', 'view', 'create', 'update'],
        'extraPatterns' => [
            'POST asdlfkasfd' => 'auditing-ios',
            'POST fskj' => 'auditing-ios',
            'POST feedback' => 'feedback',
            'GET init_app' => 'auditing',
            'GET app-check-version' => 'app-check-version',
            'POST upload-file-and-to-aliyun_oss' => 'upload-file-and-to-aliyun_oss',
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'article-collect',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'article-browse',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'city',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'suggestion',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'my-center',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'business-type',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'business-background',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'business',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'inform-dynamic',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'business-report',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'radio'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'radio-comment'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'radio-report'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'radio-comment-like'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'radio-shaft'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'search'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'business-banner-pc'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'index-banner'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'index-setting'
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-card']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-card-background']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['week-news']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['interest']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb'],
        'ruleConfig' => [
            'class' => 'yii\web\UrlRule',
            'defaults' => [
                'fields' => 'id,est_init_investment,category_name,image_url,name,direct_store_num,apply_num,brand_year',
            ]
        ],
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb-comment']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'business-banner',
        'extraPatterns' => [
        ]
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-dc-order']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-card']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-dc-report']
    ],
    'POST get-access-token' => 'user/get-access-token',
    'POST get-access-token-tpp' => 'user/get-access-token-tpp',
    'POST get-sms-code' => 'user/get-sms-code',
    'POST send-sms-code' => 'user/send-sms-code',
    'POST feedback' => 'user/feedback',
    'POST submit-device' => 'user/submit-device',
    'POST click-farm' => 'user/click-farm',
    '<controller>/<action>' => '<controller>/<action>',
];
