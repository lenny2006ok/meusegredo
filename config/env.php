<?php
// config/env.php

$config = [];

$defaults = [
    'APP_ENV' => 'production',
    'APP_URL' => 'http://localhost',
    'APP_NAME' => 'MeuSegredo.Shop',
    'DB_HOST' => 'banco-mariadb',
    'DB_PORT' => '3306',
    'DB_NAME' => 'banco_site',
    'DB_USER' => 'usuario_site',
    'DB_PASS' => 'senha_db_segura',
    'DB_CHARSET' => 'utf8mb4',
    'SESSION_NAME' => 'meusegredo_session',
    'MAX_POSTS_PER_IP_PER_DAY' => '5',
    'MAX_COMMENTS_PER_IP_PER_DAY' => '50',
    'TURNSTILE_SITE_KEY' => '',
    'TURNSTILE_SECRET_KEY' => '',
    'MODERATION_EMAIL' => 'admin@meusegredo.shop',
    'AUTO_FILTER_ENABLED' => 'true'
];

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            $config[$key] = $value;
        }
    }
}

foreach ($defaults as $key => $defaultVal) {
    if (isset($config[$key])) {
        continue;
    }

    $envVal = getenv($key);
    if ($envVal !== false) {
        $config[$key] = $envVal;
    } elseif (isset($_ENV[$key])) {
        $config[$key] = $_ENV[$key];
    } elseif (isset($_SERVER[$key])) {
        $config[$key] = $_SERVER[$key];
    } else {
        $config[$key] = $defaultVal;
    }
}

return $config;
