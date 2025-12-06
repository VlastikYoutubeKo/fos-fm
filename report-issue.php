<?php
require_once 'config.php';
requireLogin();
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $radio_name = trim($_POST['radio_name'] ?? '');
    $issue_type = trim($_POST['issue_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (empty($radio_name) || empty($issue_type)) {
        $error = 'Radio name and issue type are required!';
    } else {
        $data = json_encode(['radio_name'=>$radio_name,'issue_type'=>$issue_type,'description'=>$description]);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO pending_changes (user_id, change_type, data) VALUES (?, 'report_issue', ?)");
        $stmt->execute([$_SESSION['user_id'], $data]);
        $message = 'Issue reported!';
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - Radio Database</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <h1>📻 Radio Database</h1>
            <div class="user-info">
                <a href="/dashboard.php" class="btn btn-small">← Back</a>
                <a href="/logout.php" class="btn btn-small">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="form-container">
            <h2>Report Radio Issue</h2>
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="radio_name">Radio Name *</label>
                    <input type="text" id="radio_name" name="radio_name" required value="<?php echo htmlspecialchars($_POST['radio_name'] ?? ''); ?>">
                    <small>Name of the radio station with issues</small>
                </div>
                <div class="form-group">
                    <label for="issue_type">Issue Type *</label>
                    <select id="issue_type" name="issue_type" required>
                        <option value="">Select issue type</option>
                        <option value="stream_not_working">Stream Not Working</option>
                        <option value="low_quality">Low Quality</option>
                        <option value="wrong_url">Wrong URL</option>
                        <option value="wrong_info">Wrong Information</option>
                        <option value="duplicate">Duplicate Entry</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" placeholder="Additional details..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <small>Provide more details (optional)</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Report Issue</button>
                    <a href="/dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
