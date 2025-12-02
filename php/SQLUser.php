<?php

require 'vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (Exception $e) {
    die("Error loading environment: " . $e->getMessage());
}

$host = $_ENV['DB_HOST'];
$db = $_ENV['DB_NAME'];
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

echo "<h3>1. Initial Connection (Standard User)</h3>";
$standardUser = $_ENV['DB_ADMIN_USERNAME'];
$standardPass = $_ENV['DB_ADMIN_PASSWORD'];

try {
    $pdo_standard = new PDO($dsn, $standardUser, $standardPass, $options);
} catch (\PDOException $e) {
    die("Standard Connection failed: " . $e->getMessage());
}

function overrideToCustomerUser() {
    global $dsn, $options, $pdo_standard;

    $customerUser = 'customer_john_doe_login'; 
    $customerPass = 'john_doe_specific_sql_password'; 

    try {
        $pdo_customer = new PDO($dsn, $customerUser, $customerPass, $options);
        return $pdo_customer;
    } catch (\PDOException $e) {
        return null;
    }
}
?>