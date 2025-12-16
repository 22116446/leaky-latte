<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    die('Database configuration file (config.php) not found.');
}
$cfg = require $configPath;

foreach ($cfg as $key => $value) {
    putenv("$key=$value");
}

$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT') ?: '5432';
$dbName = getenv('DB_NAME') ?: 'postgres';
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');

if (!$dbHost || !$dbUser || !$dbPass) {
    die('Database variables missing. Check config.php.');
}

// Prevent libpq from trying to load client certs from /var/root/.postgresql
putenv('PGSSLCERT=');
putenv('PGSSLKEY=');

// SSL ON (encrypted), but skip certificate verification for local XAMPP compatibility
$dsn =
    "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName};" .
    "sslmode=require;" .
    "sslcert=;" .
    "sslkey=;";

try {
    $conn = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
