<?php
require_once 'config.php';
requireLogin();

$db = getDB();

// Get all pending changes for current user
$stmt = $db->prepare("SELECT * FROM pending_changes WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$_SESSION['user_id']]);
$changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$changeCount = count($changes);

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM pending_changes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    header('Location: /review.php');
    exit;
}

// Separate changes by type
$addRadios = [];
$reportIssues = [];

foreach ($changes as $change) {
    $change['data_decoded'] = json_decode($change['data'], true);
    if ($change['change_type'] === 'add_radio') {
        $addRadios[] = $change;
    } elseif ($change['change_type'] === 'report_issue') {
        $reportIssues[] = $change;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Changes - Radio Database</title>
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
        <h2>Review Your Changes</h2>
        <p>Total pending changes: <strong><?php echo $changeCount; ?></strong></p>
        
        <?php if ($changeCount === 0): ?>
            <div class="alert alert-info">No pending changes to review.</div>
            <a href="/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        <?php else: ?>
            
            <?php if ($changeCount > 50): ?>
                <div class="alert alert-warning">
                    ⚠️ You have more than 50 changes. This will create a large pull request.
                    <br>Consider submitting in batches for easier review.
                </div>
            <?php endif; ?>
            
            <!-- Add Radio Changes -->
            <?php if (!empty($addRadios)): ?>
            <div class="changes-section">
                <h3>➕ New Radios (<?php echo count($addRadios); ?>)</h3>
                <div class="changes-list">
                    <?php foreach ($addRadios as $change): 
                        $data = $change['data_decoded'];
                    ?>
                    <div class="change-card">
                        <div class="change-header">
                            <h4><?php echo htmlspecialchars($data['name']); ?></h4>
                            <a href="?delete=<?php echo $change['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this change?')">Delete</a>
                        </div>
                        <div class="change-details">
                            <p><strong>Stream:</strong> <?php echo htmlspecialchars($data['stream_url']); ?></p>
                            <?php if (!empty($data['url'])): ?>
                                <p><strong>Homepage:</strong> <?php echo htmlspecialchars($data['url']); ?></p>
                            <?php endif; ?>
                            <p><strong>Country:</strong> <?php echo htmlspecialchars($data['country']); ?>
                            <?php if (!empty($data['region'])): ?>
                                / <?php echo htmlspecialchars($data['region']); ?>
                            <?php endif; ?>
                            </p>
                            <?php if (!empty($data['genre'])): ?>
                                <p><strong>Genre:</strong> <?php echo htmlspecialchars($data['genre']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Report Issue Changes -->
            <?php if (!empty($reportIssues)): ?>
            <div class="changes-section">
                <h3>⚠️ Reported Issues (<?php echo count($reportIssues); ?>)</h3>
                <div class="changes-list">
                    <?php foreach ($reportIssues as $change): 
                        $data = $change['data_decoded'];
                    ?>
                    <div class="change-card">
                        <div class="change-header">
                            <h4><?php echo htmlspecialchars($data['radio_name']); ?></h4>
                            <a href="?delete=<?php echo $change['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this report?')">Delete</a>
                        </div>
                        <div class="change-details">
                            <p><strong>Issue:</strong> <?php echo htmlspecialchars(str_replace('_', ' ', ucwords($data['issue_type'], '_'))); ?></p>
                            <?php if (!empty($data['description'])): ?>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($data['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="submit-section">
                <?php if ($changeCount > 50): ?>
                    <div id="confirmDialog" style="display: none;">
                        <p>Are you sure you want to submit all <?php echo $changeCount; ?> changes in one pull request?</p>
                        <form method="POST" action="/submit.php">
                            <button type="submit" name="confirm" value="all" class="btn btn-success">Yes, Submit All</button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('confirmDialog').style.display='none'">Cancel</button>
                        </form>
                    </div>
                    <button class="btn btn-success" onclick="document.getElementById('confirmDialog').style.display='block'">Submit Pull Request (<?php echo $changeCount; ?> changes)</button>
                <?php else: ?>
                    <form method="POST" action="/submit.php">
                        <button type="submit" class="btn btn-success">Submit Pull Request (<?php echo $changeCount; ?> changes)</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
