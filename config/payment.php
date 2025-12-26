<?php
// /eaglesuite/admin/config/payment.php
return [
    'wave' => [
        'sandbox' => true,
        'sandbox_client_id' => 'XXX_WAVE_SANDBOX_CLIENT_ID',
        'sandbox_client_secret' => 'XXX_WAVE_SANDBOX_CLIENT_SECRET',
        'prod_client_id' => 'XXX_WAVE_PROD_CLIENT_ID',
        'prod_client_secret' => 'XXX_WAVE_PROD_CLIENT_SECRET',
        // endpoints sample
        'sandbox_base' => 'https://sandbox.wave.com',   // adapter si differente
        'prod_base' => 'https://api.wave.com'           // placeholder
    ],
    'orangemoney' => [
        'sandbox' => true,
        'sandbox_api_key' => 'XXX_OM_SANDBOX_KEY',
        'prod_api_key' => 'XXX_OM_PROD_KEY',
        'base' => 'https://api.orange-sonatel.com' // adapte selon doc
    ],
    'webhook' => [
        // secret pour verifier HMAC de webhooks (si dispo)
        'secret' => 'XXX_YOUR_WEBHOOK_SECRET'
    ]
];
