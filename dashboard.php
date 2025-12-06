<?php
require_once 'config.php';
requireLogin();
$user = getCurrentUser();
$db = getDB();
$stmt = $db->prepare("SELECT COUNT(*) as count FROM pending_changes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$pendingCount = $stmt->fetch()['count'];
$stmt = $db->prepare("SELECT * FROM submitted_prs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recentPRs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Radio Database</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <h1>📻 Radio Database</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($user['github_username']); ?></span>
                <a href="/logout.php" class="btn btn-small">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Pending Changes</h3>
                <p class="stat-number"><?php echo $pendingCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Submitted PRs</h3>
                <p class="stat-number"><?php echo count($recentPRs); ?></p>
            </div>
        </div>
        <div class="actions">
            <a href="/add-radio.php" class="btn btn-primary">➕ Add New Radio</a>
            <a href="/report-issue.php" class="btn btn-secondary">⚠️ Report Issue</a>
            <?php if ($pendingCount > 0): ?>
                <a href="/review.php" class="btn btn-success">👀 Review & Submit (<?php echo $pendingCount; ?>)</a>
            <?php endif; ?>
        </div>
        <?php if (!empty($recentPRs)): ?>
        <div class="recent-prs">
            <h2>Recent Pull Requests</h2>
            <table>
                <thead>
                    <tr><th>PR #</th><th>Changes</th><th>Status</th><th>Created</th><th>Link</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPRs as $pr): ?>
                    <tr>
                        <td>#<?php echo $pr['pr_number']; ?></td>
                        <td><?php echo $pr['changes_count']; ?> changes</td>
                        <td><span class="status-<?php echo $pr['status']; ?>"><?php echo ucfirst($pr['status']); ?></span></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($pr['created_at'])); ?></td>
                        <td><a href="<?php echo htmlspecialchars($pr['pr_url']); ?>" target="_blank">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
