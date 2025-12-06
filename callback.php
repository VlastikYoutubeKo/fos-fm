<?php
require_once 'includes/config.php';
if (($_GET['state'] ?? '') !== $_SESSION['oauth_state']) die('Invalid state');

$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => GITHUB_CLIENT_ID,
    'client_secret' => GITHUB_CLIENT_SECRET,
    'code' => $_GET['code'],
    'redirect_uri' => GITHUB_REDIRECT_URI
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$token = json_decode(curl_exec($ch), true)['access_token'] ?? null;
curl_close($ch);

if (!$token) die('Auth failed');

$ch = curl_init('https://api.github.com/user');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "User-Agent: App"]);
$ghUser = json_decode(curl_exec($ch), true);
curl_close($ch);

$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE github_id = ?");
$stmt->execute([$ghUser['id']]);
$exists = $stmt->fetch();

if ($exists) {
    $db->prepare("UPDATE users SET github_username=?, access_token=? WHERE github_id=?")
       ->execute([$ghUser['login'], $token, $ghUser['id']]);
    $_SESSION['user_id'] = $exists['id'];
} else {
    $db->prepare("INSERT INTO users (github_id, github_username, access_token) VALUES (?,?,?)")
       ->execute([$ghUser['id'], $ghUser['login'], $token]);
    $_SESSION['user_id'] = $db->lastInsertId();
}

header('Location: /index.php');
exit;