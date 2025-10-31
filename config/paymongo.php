<?php

return [
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'public_key' => env('PAYMONGO_PUBLIC_KEY'),
    'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
    'api_url' => env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1'),
    
    'payment_methods' => [
        'gcash' => [
            'name' => 'GCash',
            'type' => 'gcash',
            'enabled' => true,
        ],
        'grab_pay' => [
            'name' => 'GrabPay',
            'type' => 'grab_pay',
            'enabled' => true,
        ],
        'paymaya' => [
            'name' => 'PayMaya',
            'type' => 'paymaya',
            'enabled' => true,
        ],
        'card' => [
            'name' => 'Credit/Debit Card',
            'type' => 'card',
            'enabled' => true,
        ],
        'billease' => [
            'name' => 'BillEase',
            'type' => 'billease',
            'enabled' => true,
        ],
    ],
    
    'currency' => 'PHP',
    'webhook_events' => [
        'source.chargeable',
        'payment.paid',
        'payment.failed',
    ],
];
