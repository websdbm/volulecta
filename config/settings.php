<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Volulecta',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
        'secret' => $_ENV['APP_SECRET'] ?? '',
    ],
    
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'volulecta',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],

    'smtp' => [
        'host' => $_ENV['SMTP_HOST'] ?? '',
        'port' => (int)($_ENV['SMTP_PORT'] ?? 587),
        'user' => $_ENV['SMTP_USER'] ?? '',
        'pass' => $_ENV['SMTP_PASS'] ?? '',
        'from' => $_ENV['SMTP_FROM'] ?? 'noreply@volulecta.local',
    ],

    'amazon' => [
        'associate_tag_it' => $_ENV['AMAZON_ASSOCIATE_TAG_IT'] ?? '',
        'access_key' => $_ENV['AMAZON_ACCESS_KEY'] ?? '',
        'secret_key' => $_ENV['AMAZON_SECRET_KEY'] ?? '',
        'partner_tag' => $_ENV['AMAZON_PARTNER_TAG'] ?? '',
        'region' => $_ENV['AMAZON_REGION'] ?? 'it',
    ],

    'vapid' => [
        'public_key' => $_ENV['VAPID_PUBLIC_KEY'] ?? '',
        'private_key' => $_ENV['VAPID_PRIVATE_KEY'] ?? '',
        'subject' => $_ENV['VAPID_SUBJECT'] ?? 'mailto:admin@volulecta.local',
    ],

    'openai' => [
        'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
    ],
];
