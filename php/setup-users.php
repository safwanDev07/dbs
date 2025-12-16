<?php
/**
 * Database Users Setup Script
 *
 * Maakt twee SQL gebruikers aan:
 * 1. Admin user: Alle rechten BEHALVE DROP
 * 2. User user: Beperkte rechten (SELECT, INSERT, UPDATE, DELETE)
 *
 * Vereist root privileges om uit te voeren
 */

require_once __DIR__ . '/SQLUser.php';

// Database configuratie
$dbName = $_ENV['DB_NAME'] ?? 'dbsp2';
$adminUser = 'admin_user';
$adminPass = 'admin_password_secure';
$userUser = 'app_user';
$userPass = 'user_password_secure';

// Root verbinding (vereist voor user management)
$rootUser = $_ENV['DB_USER'] ?? 'root';
$rootPass = $_ENV['DB_PASS'] ?? 'root';

try {
    // Verbind als root
    $pdoRoot = new PDO("mysql:host={$host};charset=utf8mb4", $rootUser, $rootPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "<h2>Database Users Setup</h2>";
    echo "<p>Verbonden als root gebruiker</p><br>";

    // Stap 1: Verwijder bestaande users indien aanwezig
    echo "<h3>1. Opruimen bestaande gebruikers</h3>";
    $pdoRoot->exec("DROP USER IF EXISTS '{$adminUser}'@'localhost'");
    $pdoRoot->exec("DROP USER IF EXISTS '{$userUser}'@'localhost'");
    echo "✓ Bestaande gebruikers verwijderd<br><br>";

    // Stap 2: Maak Admin user aan met alle rechten BEHALVE DROP
    echo "<h3>2. Admin user aanmaken</h3>";
    $pdoRoot->exec("CREATE USER '{$adminUser}'@'localhost' IDENTIFIED BY '{$adminPass}'");
    echo "✓ Admin user '{$adminUser}' aangemaakt<br>";

    // Admin krijgt ALLE rechten behalve DROP
    $adminPrivileges = [
        'SELECT', 'INSERT', 'UPDATE', 'DELETE',
        'CREATE', 'ALTER', 'INDEX', 'REFERENCES',
        'CREATE TEMPORARY TABLES', 'LOCK TABLES',
        'CREATE ROUTINE', 'ALTER ROUTINE', 'EXECUTE'
    ];

    $pdoRoot->exec("GRANT " . implode(', ', $adminPrivileges) . " ON {$dbName}.* TO '{$adminUser}'@'localhost'");
    echo "✓ Admin rechten toegekend (alle behalve DROP)<br><br>";

    // Stap 3: Maak User user aan met beperkte rechten
    echo "<h3>3. User user aanmaken</h3>";
    $pdoRoot->exec("CREATE USER '{$userUser}'@'localhost' IDENTIFIED BY '{$userPass}'");
    echo "✓ User user '{$userUser}' aangemaakt<br>";

    // User krijgt alleen basis CRUD rechten
    $userPrivileges = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];
    $pdoRoot->exec("GRANT " . implode(', ', $userPrivileges) . " ON {$dbName}.* TO '{$userUser}'@'localhost'");
    echo "✓ User rechten toegekend (SELECT, INSERT, UPDATE, DELETE)<br><br>";

    // Stap 4: Refresh privileges
    $pdoRoot->exec("FLUSH PRIVILEGES");
    echo "✓ Privileges vernieuwd<br><br>";

    // Stap 5: Test Admin user verbinding
    echo "<h3>4. Admin user testen</h3>";
    $pdoAdmin = new PDO("mysql:host={$host};dbname={$dbName};charset=utf8mb4", $adminUser, $adminPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ Admin user kan verbinden<br>";

    // Test Admin rechten (mag NIET DROP gebruiken)
    try {
        $pdoAdmin->exec("CREATE TABLE IF NOT EXISTS test_admin (id INT PRIMARY KEY)");
        echo "✓ Admin kan CREATE gebruiken<br>";
    } catch (Exception $e) {
        echo "✗ Admin CREATE mislukt: " . $e->getMessage() . "<br>";
    }

    try {
        $pdoAdmin->exec("DROP TABLE test_admin");
        echo "✗ Admin kan DROP gebruiken (dit zou niet mogen gebeuren!)<br>";
    } catch (Exception $e) {
        echo "✓ Admin kan geen DROP gebruiken: " . $e->getMessage() . "<br>";
    }

    // Stap 6: Test User user verbinding
    echo "<h3>5. User user testen</h3>";
    $pdoUser = new PDO("mysql:host={$host};dbname={$dbName};charset=utf8mb4", $userUser, $userPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ User user kan verbinden<br>";

    // Test User rechten
    try {
        $pdoUser->exec("INSERT INTO user (email, wachtwoord) VALUES ('test@example.com', 'hashedpass')");
        echo "✓ User kan INSERT gebruiken<br>";
    } catch (Exception $e) {
        echo "✗ User INSERT mislukt: " . $e->getMessage() . "<br>";
    }

    try {
        $result = $pdoUser->query("SELECT COUNT(*) as count FROM user");
        $row = $result->fetch();
        echo "✓ User kan SELECT gebruiken (aantal users: {$row['count']})<br>";
    } catch (Exception $e) {
        echo "✗ User SELECT mislukt: " . $e->getMessage() . "<br>";
    }

    try {
        $pdoUser->exec("CREATE TABLE test_user (id INT PRIMARY KEY)");
        echo "✗ User kan CREATE gebruiken (dit zou niet mogen gebeuren!)<br>";
    } catch (Exception $e) {
        echo "✓ User kan geen CREATE gebruiken: " . $e->getMessage() . "<br>";
    }

    // Stap 7: Opruimen test data
    echo "<h3>6. Opruimen</h3>";
    try {
        $pdoAdmin->exec("DELETE FROM user WHERE email = 'test@example.com'");
        echo "✓ Test data verwijderd<br>";
    } catch (Exception $e) {
        echo "✗ Test data opruimen mislukt: " . $e->getMessage() . "<br>";
    }

    echo "<br><h2>Setup voltooid!</h2>";
    echo "<h3>Gebruikersoverzicht:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Gebruiker</th><th>Wachtwoord</th><th>Rechten</th><th>Wat kan wel</th><th>Wat kan niet</th></tr>";
    echo "<tr><td>{$adminUser}</td><td>{$adminPass}</td><td>Alle behalve DROP</td><td>";
    echo "SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE ROUTINE, ALTER ROUTINE, EXECUTE";
    echo "</td><td>DROP (tabellen/databases verwijderen)</td></tr>";
    echo "<tr><td>{$userUser}</td><td>{$userPass}</td><td>CRUD alleen</td><td>";
    echo "SELECT, INSERT, UPDATE, DELETE";
    echo "</td><td>CREATE, DROP, ALTER, INDEX, REFERENCES, etc.</td></tr>";
    echo "</table>";

    echo "<br><h3>Gebruik in je applicatie:</h3>";
    echo "<p><strong>Voor admin taken:</strong> Gebruik '{$adminUser}' met wachtwoord '{$adminPass}'</p>";
    echo "<p><strong>Voor normale app taken:</strong> Gebruik '{$userUser}' met wachtwoord '{$userPass}'</p>";
    echo "<p><strong>Voorbeeld code:</strong></p>";
    echo "<pre>";
    echo "\$pdoAdmin = new PDO('mysql:host=localhost;dbname={$dbName}', '{$adminUser}', '{$adminPass}');\n";
    echo "\$pdoUser = new PDO('mysql:host=localhost;dbname={$dbName}', '{$userUser}', '{$userPass}');\n";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Fout tijdens setup:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Zorg ervoor dat je root credentials correct zijn in je .env bestand.</p>";
}
?>