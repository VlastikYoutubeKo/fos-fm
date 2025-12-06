<?php
require_once 'includes/config.php';
$params = [
    'client_id' => GITHUB_CLIENT_ID,
    'redirect_uri' => GITHUB_REDIRECT_URI,
    'scope' => 'public_repo user:email',
    'state' => bin2hex(random_bytes(16))
];
$_SESSION['oauth_state'] = $params['state'];
header('Location: https://github.com/login/oauth/authorize?' . http_build_query($params));
exit;