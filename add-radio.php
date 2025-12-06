<?php
require_once 'config.php';
requireLogin();
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $stream_url = trim($_POST['stream_url'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    if (empty($name) || empty($stream_url) || empty($country)) {
        $error = 'Name, Stream URL and Country are required!';
    } elseif (!filter_var($stream_url, FILTER_VALIDATE_URL)) {
        $error = 'Invalid stream URL!';
    } elseif (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'Invalid homepage URL!';
    } else {
        $data = json_encode(['name'=>$name,'stream_url'=>$stream_url,'url'=>$url,'country'=>$country,'region'=>$region,'genre'=>$genre]);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pending_changes (user_id, change_type, data) VALUES (?, 'add_radio', ?)");
        $stmt->execute([$_SESSION['user_id'], $data]);
        $message = 'Radio added to pending changes!';
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Radio - Radio Database</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <h1>📻 Radio Database</h1>
            <div class="user-info">
                <a href="/dashboard.php" class="btn btn-small">← Back to Dashboard</a>
                <a href="/logout.php" class="btn btn-small">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="form-container">
            <h2>Add New Radio Station</h2>
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Radio Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="stream_url">Stream URL *</label>
                    <input type="url" id="stream_url" name="stream_url" required placeholder="https://stream.example.com/radio.mp3" value="<?php echo htmlspecialchars($_POST['stream_url'] ?? ''); ?>">
                    <small>Direct link to the radio stream</small>
                </div>
                <div class="form-group">
                    <label for="url">Homepage URL</label>
                    <input type="url" id="url" name="url" placeholder="https://example.com" value="<?php echo htmlspecialchars($_POST['url'] ?? ''); ?>">
                    <small>Official website (optional)</small>
                </div>
                <div class="form-group">
                    <label for="country">Country *</label>
                    <input type="text" id="country" name="country" required placeholder="CZ" value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                    <small>ISO country code (e.g., CZ, SK, PL)</small>
                </div>
                <div class="form-group">
                    <label for="region">Region</label>
                    <input type="text" id="region" name="region" placeholder="Prague, Bratislava..." value="<?php echo htmlspecialchars($_POST['region'] ?? ''); ?>">
                    <small>City or region (optional)</small>
                </div>
                <div class="form-group">
                    <label for="genre">Genre</label>
                    <input type="text" id="genre" name="genre" placeholder="Rock, Pop, News..." value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>">
                    <small>Music genre or type (optional)</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add to Pending</button>
                    <a href="/dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
