<?php

class DB {
    private $adminPdo;
    private $appPdo;

    private $host;
    private $dbName;
    private $charset;
    private $adminUser;
    private $adminPass;
    private $appUser;
    private $appPass;

    public function __construct(array $config = []) {
        $this->host = $config['host'] ?? 'localhost';
        $this->dbName = $config['name'] ?? 'dbsp2';
        $this->charset = $config['charset'] ?? 'utf8mb4';
        $this->adminUser = $config['admin_user'] ?? 'root';
        $this->adminPass = $config['admin_pass'] ?? 'root';
        $this->appUser = $config['app_user'] ?? $this->adminUser;
        $this->appPass = $config['app_pass'] ?? $this->adminPass;

        $this->connectAdmin();
    }

    public function start() {
        try {
            $this->createDatabase();
            $this->connectApp();
            $this->createTables();
        } catch (PDOException $e) {
            // bubble up or log as needed
            throw $e;
        }
    }

    private function connectAdmin() {
        $dsn = "mysql:host={$this->host};charset={$this->charset}";
        try {
            $this->adminPdo = new PDO($dsn, $this->adminUser, $this->adminPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            $this->adminPdo = null;
        }
    }

    private function connectApp() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
        try {
            $this->appPdo = new PDO($dsn, $this->appUser, $this->appPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            $this->appPdo = null;
        }
    }

    private function createDatabase() {
        if (!$this->adminPdo) return;

        $sql = "CREATE DATABASE IF NOT EXISTS `{$this->dbName}` CHARACTER SET {$this->charset} COLLATE {$this->charset}_unicode_ci";
        $this->adminPdo->exec($sql);
    }

    private function createTables() {
        if (!$this->appPdo) return;

        // Maak users tabel
        $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash CHAR(60) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset};";

        // Maak posts tabel
        $sqlPosts = "CREATE TABLE IF NOT EXISTS posts (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET={$this->charset};";

        $this->appPdo->exec($sqlUsers);
        $this->appPdo->exec($sqlPosts);
    }
}

?>

