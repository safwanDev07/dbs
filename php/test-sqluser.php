<?php
// Test script for SQLUser.php connections
require __DIR__ . '/SQLUser.php';

echo "<h2>1) Standard connection (from SQLUser.php)</h2>";
if (isset($pdo_standard) && $pdo_standard instanceof PDO) {
    echo "<p style=\"color:green\">Standard connection OK</p>";
    try {
        $stmt = $pdo_standard->query('SELECT 1');
        $val = $stmt->fetchColumn();
        echo "<p>Test query result: " . htmlspecialchars($val) . "</p>";
    } catch (Exception $e) {
        echo "<p style=\"color:orange\">Query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style=\"color:red\">Standard connection not available (check .env or credentials)</p>";
}

echo "<h2>2) Override / customer user (optional)</h2>";
// Attempt to use credentials from .env (if present) or demonstrate how to call overrideToCustomerUser
$appUser = $_ENV['DB_USER'] ?? null;
$appPass = $_ENV['DB_PASS'] ?? null;

if ($appUser) {
    $pdoApp = overrideToCustomerUser($appUser, $appPass);
    if ($pdoApp) {
        echo "<p style=\"color:green\">App user connection OK ({$appUser})</p>";
        try {
            $stmt = $pdoApp->query('SELECT 1');
            $val = $stmt->fetchColumn();
            echo "<p>App test query result: " . htmlspecialchars($val) . "</p>";
        } catch (Exception $e) {
            echo "<p style=\"color:orange\">App query failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style=\"color:red\">App user connection failed (credentials from .env)</p>";
    }
} else {
    echo "<p>No app credentials found in .env. To test override, call <code>overrideToCustomerUser('username','password')</code>.</p>";
}

echo "<hr><p>Lokale test instructies below.</p>";
?>
