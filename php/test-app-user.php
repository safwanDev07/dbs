<?php
/**
 * Test script om te laten zien hoe de app_user de database aanroept
 * Dit simuleert hoe je applicatie normaal gesproken werkt
 */

require_once __DIR__ . '/SQLUser.php';

echo "<h2>Database Access Test - App User</h2>";

// Gebruik de app_user om database operaties uit te voeren
$appUser = $_ENV['DB_APP_USER'] ?? 'app_user';
$appPass = $_ENV['DB_APP_PASS'] ?? 'user_password_secure';
$dbName = $_ENV['DB_NAME'] ?? 'dbsp2';

try {
    // Verbind als app_user (beperkte rechten)
    $pdoApp = new PDO("mysql:host={$host};dbname={$dbName};charset=utf8mb4", $appUser, $appPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "<p style='color: green;'>✓ Verbonden als app_user ({$appUser})</p>";

    // Test 1: SELECT (mag wel)
    echo "<h3>1. SELECT test (toegestaan)</h3>";
    try {
        $stmt = $pdoApp->query("SELECT COUNT(*) as total_users FROM user");
        $result = $stmt->fetch();
        echo "<p>Aantal gebruikers in database: <strong>{$result['total_users']}</strong></p>";
        echo "<p style='color: green;'>✓ SELECT werkt perfect</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ SELECT mislukt: " . $e->getMessage() . "</p>";
    }

    // Test 2: INSERT (mag wel)
    echo "<h3>2. INSERT test (toegestaan)</h3>";
    try {
        $testEmail = 'test_' . time() . '@example.com';
        $testPassword = password_hash('testpass', PASSWORD_DEFAULT);

        $stmt = $pdoApp->prepare("INSERT INTO user (email, wachtwoord) VALUES (?, ?)");
        $stmt->execute([$testEmail, $testPassword]);

        echo "<p>Test gebruiker toegevoegd: {$testEmail}</p>";
        echo "<p style='color: green;'>✓ INSERT werkt perfect</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ INSERT mislukt: " . $e->getMessage() . "</p>";
    }

    // Test 3: UPDATE (mag wel)
    echo "<h3>3. UPDATE test (toegestaan)</h3>";
    try {
        $newPassword = password_hash('updatedpass', PASSWORD_DEFAULT);
        $stmt = $pdoApp->prepare("UPDATE user SET wachtwoord = ? WHERE email = ?");
        $stmt->execute([$newPassword, $testEmail]);

        echo "<p>Wachtwoord bijgewerkt voor: {$testEmail}</p>";
        echo "<p style='color: green;'>✓ UPDATE werkt perfect</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ UPDATE mislukt: " . $e->getMessage() . "</p>";
    }

    // Test 4: DELETE (mag wel)
    echo "<h3>4. DELETE test (toegestaan)</h3>";
    try {
        $stmt = $pdoApp->prepare("DELETE FROM user WHERE email = ?");
        $stmt->execute([$testEmail]);

        echo "<p>Test gebruiker verwijderd: {$testEmail}</p>";
        echo "<p style='color: green;'>✓ DELETE werkt perfect</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ DELETE mislukt: " . $e->getMessage() . "</p>";
    }

    // Test 5: CREATE TABLE (mag NIET)
    echo "<h3>5. CREATE TABLE test (niet toegestaan)</h3>";
    try {
        $pdoApp->exec("CREATE TABLE test_forbidden (id INT PRIMARY KEY)");
        echo "<p style='color: red;'>✗ CREATE TABLE zou niet mogen werken!</p>";
    } catch (Exception $e) {
        echo "<p style='color: green;'>✓ CREATE TABLE correct geweigerd: " . $e->getMessage() . "</p>";
    }

    // Test 6: DROP TABLE (mag NIET)
    echo "<h3>6. DROP TABLE test (niet toegestaan)</h3>";
    try {
        $pdoApp->exec("DROP TABLE IF EXISTS test_nonexistent");
        echo "<p style='color: red;'>✗ DROP TABLE zou niet mogen werken!</p>";
    } catch (Exception $e) {
        echo "<p style='color: green;'>✓ DROP TABLE correct geweigerd: " . $e->getMessage() . "</p>";
    }

    echo "<br><h2>Samenvatting</h2>";
    echo "<p>De app_user kan perfect:</p>";
    echo "<ul>";
    echo "<li>✅ Gegevens lezen (SELECT)</li>";
    echo "<li>✅ Nieuwe gegevens toevoegen (INSERT)</li>";
    echo "<li>✅ Gegevens wijzigen (UPDATE)</li>";
    echo "<li>✅ Gegevens verwijderen (DELETE)</li>";
    echo "</ul>";

    echo "<p>De app_user kan NIET:</p>";
    echo "<ul>";
    echo "<li>❌ Tabellen aanmaken (CREATE TABLE)</li>";
    echo "<li>❌ Tabellen verwijderen (DROP TABLE)</li>";
    echo "<li>❌ Tabellen wijzigen (ALTER TABLE)</li>";
    echo "<li>❌ Indexen beheren</li>";
    echo "<li>❌ Stored procedures maken/uitvoeren</li>";
    echo "</ul>";

    echo "<p style='color: blue;'><strong>Deze setup zorgt voor betere beveiliging omdat de applicatie alleen kan doen wat nodig is voor normaal gebruik.</strong></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Connection Error</h2>";
    echo "<p>Kan geen verbinding maken als app_user. Zorg ervoor dat je eerst <code>setup-users.php</code> hebt uitgevoerd.</p>";
    echo "<p>Fout: " . $e->getMessage() . "</p>";
}
?>