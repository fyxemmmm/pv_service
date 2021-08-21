<?php
return [
    'aliyun' => [
        'dysms' => [
            'AccessKeyID' => 'LTAI3g2juJH1VkdX',
            'AccessKeySecret' => 'KvwBi9oQDeX30oXy3OWbDsAoeW2mJ5',
            'template' => [
                'SMS_168665114', // '验证码${code}，您正在进行身份验证，打死不要告诉别人哦！'
            ],
            'sign_name' => [
                '犀京',
            ]
        ],
        'xijin_active' => [
            'AccessKeyID' => 'LTAI3g2juJH1VkdX',
            'AccessKeySecret' => 'KvwBi9oQDeX30oXy3OWbDsAoeW2mJ5',
            'template' => [
                'SMS_174987858',
            ],
            'sign_name' => [
                '犀京',
            ]
        ],
        'sshua_active' => [
            'AccessKeyID' => 'LTAI3g2juJH1VkdX',
            'AccessKeySecret' => 'KvwBi9oQDeX30oXy3OWbDsAoeW2mJ5',
            'template' => [
                'SMS_174987976',
            ],
            'sign_name' => [
                '犀京',
            ]
        ],
        'cqqb_active' => [
            'AccessKeyID' => 'LTAI3g2juJH1VkdX',
            'AccessKeySecret' => 'KvwBi9oQDeX30oXy3OWbDsAoeW2mJ5',
            'template' => [
                'SMS_174992867',
            ],
            'sign_name' => [
                '犀京',
            ]
        ]
    ],
    "aliyun_oss" => [
        "AccessKeyID" => "LTAI5chMr1Mclwm9",
        "AccessKeySecret" => "rwDEQeWRhxMbO1G4heADvqWwTCCiB8",
        "EndPoint" => "http://oss-cn-shanghai.aliyuncs.com",
        "REGION" =>"oss-cn-shanghai",
        "Bucket" => [
            "xijin"
        ]
    ],
    "aliyun_sts" => [
        'AccessKeyID' => 'LTAI3g2juJH1VkdX',
        'AccessKeySecret' => 'KvwBi9oQDeX30oXy3OWbDsAoeW2mJ5',
        'REGION_ID' => 'cn-shanghai',
        "ENDPOINT" => "sts.cn-shanghai.aliyuncs.com",
        'roleArn' => 'acs:ram::1052357993023877:role/osstempadmin',
    ],
    'share_url' => 'https://api.xykj1.com/radio_share' // share_url的服务器地址
];
