<?php
require_once 'config.php';
requireLogin();

$prNumber = $_GET['pr'] ?? '';
$prUrl = $_GET['url'] ?? '';

if (empty($prNumber) || empty($prUrl)) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success - Radio Database</title>
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
        <div class="success-box">
            <div class="success-icon">✅</div>
            <h2>Pull Request Created Successfully!</h2>
            
            <div class="pr-info">
                <p>Your changes have been submitted as pull request <strong>#<?php echo htmlspecialchars($prNumber); ?></strong></p>
                <p>Once reviewed and merged, your radio stations will be added to the database.</p>
            </div>
            
            <div class="actions">
                <a href="<?php echo htmlspecialchars($prUrl); ?>" target="_blank" class="btn btn-primary">View Pull Request on GitHub</a>
                <a href="/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="/add-radio.php" class="btn btn-success">Add More Radios</a>
            </div>
            
            <div class="info-box">
                <h3>What happens next?</h3>
                <ol>
                    <li>Repository maintainers will review your submission</li>
                    <li>They may ask for changes or clarifications</li>
                    <li>Once approved, your PR will be merged</li>
                    <li>Your radios will be available in the database</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
