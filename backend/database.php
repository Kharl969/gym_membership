<?php
declare(strict_types=1);

$localConfigPath = __DIR__ . '/database.config.php';
$databaseConfig = is_file($localConfigPath) ? require $localConfigPath : [];

$host = $databaseConfig['host'] ?? (getenv('GYM_DB_HOST') ?: 'localhost');
$dbname = $databaseConfig['dbname'] ?? (getenv('GYM_DB_NAME') ?: 'gym_membership_db');
$username = $databaseConfig['username'] ?? (getenv('GYM_DB_USERNAME') ?: 'root');
$password = $databaseConfig['password'] ?? (getenv('GYM_DB_PASSWORD') ?: '');

try {
    $conn = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Gym database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Could not connect to the database. Check the backend database settings.');
}
