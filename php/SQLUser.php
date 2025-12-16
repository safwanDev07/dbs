<?php

// Simple .env loader (no composer required). It will populate $_ENV with key=value lines.
function loadDotEnvFile(string $path)
{
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key !== '') {
            $_ENV[$key] = $value;
        }
    }
}

// Try to load .env in same directory
loadDotEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env');

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db = $_ENV['DB_NAME'] ?? '';
$charset = 'utf8mb4';

// Default credentials (match your project's .env.example)
$standardUser = $_ENV['DB_USER'] ?? 'root';
$standardPass = $_ENV['DB_PASS'] ?? 'root';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// If a database name exists, use it in DSN; otherwise connect to server only.
$dsnWithDb = "mysql:host={$host};dbname={$db};charset={$charset}";
$dsnNoDb = "mysql:host={$host};charset={$charset}";

// Try connect using provided user to the database if possible, otherwise to server
try {
    if ($db !== '') {
        $pdo_standard = new PDO($dsnWithDb, $standardUser, $standardPass, $options);
    } else {
        $pdo_standard = new PDO($dsnNoDb, $standardUser, $standardPass, $options);
    }
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Optional helper: return a PDO instance for a specific user (useful for testing different SQL users)
function overrideToCustomerUser(string $username, string $password): ?PDO
{
    global $host, $db, $charset, $options;
    $dsn = $db ? "mysql:host={$host};dbname={$db};charset={$charset}" : "mysql:host={$host};charset={$charset}";
    try {
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        return null;
    }
}

// Example usage (commented):
// $pdoCustomer = overrideToCustomerUser('app_user', 'app_password');
// if ($pdoCustomer) { echo "Customer connection OK"; } else { echo "Customer connection failed"; }

?>