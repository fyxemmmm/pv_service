<?php
return [
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['admin', 'article', 'user'],
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['article-type']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['label']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['article-label']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['article-comment']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['banner']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['activity']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-type']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-background']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-card-background']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-banner']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['inform-dc']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['city']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['suggestion']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['radio']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['radio-shaft']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['radio-comment']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['index-banner']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['index-setting']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['business-banner-pc']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['week-news']
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
        'controller' => ['article-type-insert']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['interest']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['interest-type']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['interest-card']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb-banner']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb-hot-prefecture']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb-cate']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['jmb-sub-cate']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['article-dc-desc']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-apply-price']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-dc-order']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['user-dc-report']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['goods']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['goods-order']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['goods-specification']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['goods-type']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['index-banner-xijing']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => ['coupon']
    ],
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => 'general',
        'except' => ['index', 'view', 'create', 'update'],
        'extraPatterns' => [
            'POST upload-file-and-to-aliyun_oss' => 'upload-file-and-to-aliyun_oss'
        ]
    ],
    'POST get-access-token' => 'admin/get-access-token',
    '<controller>/<action>' => '<controller>/<action>',
    'POST upload-to-aliyun_oss' => 'common/upload-to-aliyun_oss'
];