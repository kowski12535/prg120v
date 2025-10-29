<?php
declare(strict_types=1);

/**
 * Standalone PDO connection helper for environments that include db.php directly.
 */
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=fadac3356;charset=utf8mb4',
        'fadac3356',
        '1a76fadac3356',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
} catch (PDOException $exception) {
    http_response_code(500);
    echo '<h1>Database connection failed</h1>';
    echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    exit;
}
