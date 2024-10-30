<?php

return [

    'signature' => env('SMS_SIGNATURE', ''),

    'expired_at' => env('SMS_EXPIRED_AT', 5),

    'driver' => env('SMS_DRIVER', 'tencent'),

    'drivers' => [

        'tencent' => [
            'secret_id' => env('TENCENT_CLOUD_ID', ''),
            'secret_key' => env('TENCENT_CLOUD_KEY', ''),
            'sms_app_id' => env('TENCENT_SMS_APPID', ''),
            'template_id' => env('TENCENT_SMS_TEMPLATE', ''),
            'region' => 'ap-beijing',
        ],

    ]
];