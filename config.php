<?php
session_start();

// Load .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Define constants
define('GITHUB_CLIENT_ID', $_ENV['GITHUB_CLIENT_ID'] ?? '');
define('GITHUB_CLIENT_SECRET', $_ENV['GITHUB_CLIENT_SECRET'] ?? '');
define('GITHUB_REDIRECT_URI', $_ENV['GITHUB_REDIRECT_URI'] ?? '');
define('GITHUB_REPO_OWNER', $_ENV['GITHUB_REPO_OWNER'] ?? '');
define('GITHUB_REPO_NAME', $_ENV['GITHUB_REPO_NAME'] ?? '');
define('GITHUB_JSON_FILE', $_ENV['GITHUB_JSON_FILE'] ?? 'radios.json');
define('DB_PATH', $_ENV['DB_PATH'] ?? __DIR__ . '/data/database.db');
define('APP_URL', $_ENV['APP_URL'] ?? '');
define('SESSION_SECRET', $_ENV['SESSION_SECRET'] ?? 'change_me');

// Database connection helper
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $db;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['github_username']);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit;
    }
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
