<?php
session_start();

// Load .env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Config
define('APP_NAME', 'FOS FM');
define('GITHUB_CLIENT_ID', $_ENV['GITHUB_CLIENT_ID'] ?? '');
define('GITHUB_CLIENT_SECRET', $_ENV['GITHUB_CLIENT_SECRET'] ?? '');
define('GITHUB_REDIRECT_URI', $_ENV['GITHUB_REDIRECT_URI'] ?? '');
define('GITHUB_REPO_OWNER', $_ENV['GITHUB_REPO_OWNER'] ?? '');
define('GITHUB_REPO_NAME', $_ENV['GITHUB_REPO_NAME'] ?? '');
define('GITHUB_JSON_FILE', $_ENV['GITHUB_JSON_FILE'] ?? 'radios.json');
define('DB_PATH', $_ENV['DB_PATH'] ?? __DIR__ . '/../data/database.db');

// Database
function getDB() {
    static $db = null;
    if ($db === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $db;
}

// Helpers
function isLoggedIn() { return isset($_SESSION['user_id']); }

// Fetch Public Radios (No Token Required)
function getPublicRadios() {
    $owner = GITHUB_REPO_OWNER;
    $repo = GITHUB_REPO_NAME;
    $file = GITHUB_JSON_FILE;
    $url = "https://raw.githubusercontent.com/$owner/$repo/main/$file";
    
    $opts = ["http" => ["method" => "GET", "header" => "User-Agent: FOS-FM-App\r\n"]];
    $context = stream_context_create($opts);
    
    $json = @file_get_contents($url, false, $context);
    return $json ? (json_decode($json, true) ?? []) : [];
}
?>