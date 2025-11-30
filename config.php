<?php
declare(strict_types=1);

define('DATA_PATH', BASE_PATH . '/data');
define('APP_NAME', 'HR Assistant');
define('APP_VERSION', '1.0.0');

if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0755, true);
}

$defaultConfig = [
    'telegramBotToken' => '',
    'telegramMode' => 'webhook',
    'webhookUrl' => '',
    'mailcow' => [
        'url' => 'https://mail.example.com',
        'apiKey' => ''
    ],
    'gitlab' => [
        'url' => 'https://gitlab.example.com',
        'token' => ''
    ],
    'keycloak' => [
        'url' => 'https://auth.example.com',
        'realm' => 'hr-assistant-realm',
        'clientId' => 'hr-assistant-client',
        'clientSecret' => ''
    ],
    'emailService' => [
        'imapHost' => 'imap.example.com',
        'imapUser' => 'hr-assistant@example.com',
        'imapPass' => '',
        'smtpHost' => 'smtp.example.com'
    ]
];
