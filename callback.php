<?php
require_once 'config.php';
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) die('Invalid state');
if (!isset($_GET['code'])) die('No authorization code');

$tokenUrl = 'https://github.com/login/oauth/access_token';
$postData = [
    'client_id' => GITHUB_CLIENT_ID,
    'client_secret' => GITHUB_CLIENT_SECRET,
    'code' => $_GET['code'],
    'redirect_uri' => GITHUB_REDIRECT_URI
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);
if (!isset($tokenData['access_token'])) die('Failed to obtain access token');
$accessToken = $tokenData['access_token'];

$userUrl = 'https://api.github.com/user';
$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App'
]);
$userResponse = curl_exec($ch);
curl_close($ch);

$userData = json_decode($userResponse, true);
if (!isset($userData['id'])) die('Failed to get user information');

$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE github_id = ?");
$stmt->execute([$userData['id']]);
$existingUser = $stmt->fetch();

if ($existingUser) {
    $stmt = $db->prepare("UPDATE users SET github_username = ?, access_token = ? WHERE github_id = ?");
    $stmt->execute([$userData['login'], $accessToken, $userData['id']]);
    $userId = $existingUser['id'];
} else {
    $stmt = $db->prepare("INSERT INTO users (github_id, github_username, access_token) VALUES (?, ?, ?)");
    $stmt->execute([$userData['id'], $userData['login'], $accessToken]);
    $userId = $db->lastInsertId();
}

$_SESSION['user_id'] = $userId;
$_SESSION['github_username'] = $userData['login'];
$_SESSION['github_id'] = $userData['id'];

header('Location: /dashboard.php');
exit;
