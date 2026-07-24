<?php
// config/database.php

$config = require __DIR__ . '/env.php';

$host = $config['DB_HOST'] ?? 'banco-mariadb';
$port = $config['DB_PORT'] ?? '3306';
$dbname = $config['DB_NAME'] ?? 'banco_site';
$user = $config['DB_USER'] ?? 'usuario_site';
$pass = $config['DB_PASS'] ?? 'senha_db_segura';
$charset = $config['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    if (($config['APP_ENV'] ?? 'production') === 'development') {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } else {
        error_log("Database connection failure: " . $e->getMessage());
        http_response_code(500);
        die("<!DOCTYPE html><html><head><title>Erro de Conexão</title><style>body { background-color: #0a0a0a; color: #f5f5f5; font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; }</style></head><body><h1>Desculpe-nos</h1><p>Não foi possível conectar ao banco de dados no momento. Por favor, tente novamente mais tarde.</p></body></html>");
    }
}

return [
    'pdo' => $pdo
];
