<?php
// Configuration de la base de données pour TrustPick
return [
    'db_host' => '127.0.0.1',
    'db_name' => 'trustpick_v2',
    'db_user' => 'root',
    'db_pass' => '',

    // Configuration des paiements Mobile Money via MeSomb
    'payment' => [
        // MeSomb API (Cameroun - Orange Money & MTN Mobile Money)
        'mesomb' => [
            'application_key' => '18bfc8002ab9555601c82fcb07e2817e221dad36',
            'access_key' => '5c63e664-2993-4f11-9cea-54347347a307',
            'secret_key' => 'd68f6eb3-9a8b-4315-8228-587d6f25c2a4',
            'api_url' => 'https://mesomb.hachther.com/api/v1.1',
            'enabled' => true
        ],

        // Comptes de réception
        'receiving_accounts' => [
            'orange_money' => '657317490',
            'mtn_money' => '683833646'
        ],

        // Configuration générale
        'min_deposit' => 5000,
        'currency' => 'XAF' // Franc CFA
    ]
];