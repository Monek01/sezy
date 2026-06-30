<?php

return [

    // "mock" : simule les paiements localement (aucun appel réseau, validation
    //          automatique après quelques secondes) -> utilisé tant que les
    //          comptes marchands ne sont pas obtenus.
    // "live" : appelle réellement les API des opérateurs / agrégateurs.
    'mode' => env('PAYMENT_MODE', 'mock'),

    'currency' => 'XOF',

    'methods' => [
        'mtn_momo' => [
            'label' => 'MTN Mobile Money',
            'driver' => \App\Services\Payment\MtnMomoGateway::class,
            'logo' => '/images/payments/mtn-momo.svg',
            'enabled' => true,
            'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
            'api_user' => env('MTN_MOMO_API_USER'),
            'api_key' => env('MTN_MOMO_API_KEY'),
            'target_environment' => env('MTN_MOMO_TARGET_ENV', 'sandbox'),
        ],
        'moov_money' => [
            'label' => 'Moov Money',
            'driver' => \App\Services\Payment\MoovMoneyGateway::class,
            'logo' => '/images/payments/moov-money.svg',
            'enabled' => true,
            'api_key' => env('MOOV_MONEY_API_KEY'),
            'merchant_id' => env('MOOV_MONEY_MERCHANT_ID'),
        ],
        'wave' => [
            'label' => 'Wave',
            'driver' => \App\Services\Payment\WaveGateway::class,
            'logo' => '/images/payments/wave.svg',
            'enabled' => true,
            'api_key' => env('WAVE_API_KEY'),
            'merchant_id' => env('WAVE_MERCHANT_ID'),
        ],
        'card' => [
            'label' => 'Carte bancaire (Visa / Mastercard)',
            'driver' => \App\Services\Payment\PayDunyaCardGateway::class,
            'logo' => '/images/payments/card.svg',
            'enabled' => true,
            'master_key' => env('PAYDUNYA_MASTER_KEY'),
            'private_key' => env('PAYDUNYA_PRIVATE_KEY'),
            'public_key' => env('PAYDUNYA_PUBLIC_KEY'),
            'token' => env('PAYDUNYA_TOKEN'),
            'paydunya_mode' => env('PAYDUNYA_MODE', 'test'),
        ],
    ],

    // Délai simulé (en secondes) avant qu'un paiement "mock" passe à "réussi".
    // Permet de tester côté front l'état "en attente de confirmation opérateur".
    'mock_delay_seconds' => 5,
];
