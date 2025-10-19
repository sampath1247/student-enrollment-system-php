<?php
/**
 * Central DB configuration using environment variables.
 * Never hardcode credentials in source files.
 */

function env_or_default(string $key, $default = null) {
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

function getDbConnection(): PDO {
    $host = env_or_default('DB_HOST', '127.0.0.1');
    $port = env_or_default('DB_PORT', '3306');
    $db   = env_or_default('DB_NAME', 'enrollment_db');
    $user = env_or_default('DB_USER', 'root');
    $pass = env_or_default('DB_PASS', '');

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $user, $pass, $options);
}
