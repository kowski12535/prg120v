<?php
declare(strict_types=1);

/**
 * Returns a shared PDO connection using environment configuration.
 *
 * Expected environment variables:
 * - DB_HOST (default: localhost)
 * - DB_NAME (default: prg120v)
 * - DB_USER (default: root)
 * - DB_PASS (default: empty)
 */
function getPDO()
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    if (!class_exists('PDO')) {
        http_response_code(500);
        echo '<h1>Mangler PDO-utvidelsen</h1>';
        echo '<p>Tjenesten din må ha PHPs PDO- og pdo_mysql-utvidelser aktivert for at databasen skal fungere.</p>';
        exit;
    }

    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'fadac3356';
    $dbUser = getenv('DB_USER') ?: 'fadac3356';
    $dbPass = getenv('DB_PASS') ?: '1a76fadac3356';

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);

    try {
        $pdo = new PDO(
            $dsn,
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );
    } catch (Throwable $exception) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        exit;
    }

    return $pdo;
}
