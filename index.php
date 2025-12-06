<?php
require_once 'includes/config.php';
$db = getDB();
$radios = getPublicRadios();
$user = null;
$pendingChanges = [];

// Load user data
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    $stmt = $db->prepare("SELECT * FROM pending_changes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingChanges = $stmt->fetchAll();
}
$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Toggle between Add and Report forms
        function toggleForm(type) {
            document.getElementById('form-add').style.display = type === 'add' ? 'block' : 'none';
            document.getElementById('form-report').style.display = type === 'report' ? 'block' : 'none';
            
            // Update tabs
            document.getElementById('btn-add').classList.toggle('active', type === 'add');
            document.getElementById('btn-report').classList.toggle('active', type === 'report');
        }

        // Smart function: Opens report form AND fills the name
        function prefillReport(radioName) {
            // 1. Switch to Report Tab
            toggleForm('report');
            
            // 2. Fill the Input
            const input = document.querySelector('input[name="radio_name"]');
            if(input) {
                input.value = radioName;
                input.focus();
                
                // 3. Smooth scroll to top (for mobile users)
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                alert("Please login to report issues!");
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    </script>
</head>
<body>

<nav class="navbar">
    <div class="container flex-between">
        <div class="logo">
            <span style="font-size: 1.5rem;">⚡</span> <?php echo APP_NAME; ?>
        </div>
        <?php if ($user): ?>
            <div style="display: flex; gap: 15px; align-items: center;">
                <span style="font-size: 0.9rem; color: #a1a1aa;"><?php echo htmlspecialchars($user['github_username']); ?></span>
                <a href="/logout.php" class="btn btn-secondary btn-sm">Logout</a>
            </div>
        <?php else: ?>
            <a href="/auth.php" class="btn btn-primary btn-sm">Login via GitHub</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">

    <?php if ($status === 'pr_created'): ?>
        <div class="card" style="border-color: var(--success); background: rgba(16, 185, 129, 0.05); margin-bottom: 2rem;">
            <h3 style="color: var(--success); margin-bottom: 0.5rem;">🚀 Successfully Submitted!</h3>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">Your changes have been sent to GitHub for review.</p>
            <a href="<?php echo htmlspecialchars($_GET['url'] ?? '#'); ?>" target="_blank" class="btn btn-success btn-sm">View Pull Request &rarr;</a>
        </div>
    <?php endif; ?>

    <?php if (!$user): ?>
    <div class="text-center" style="padding: 4rem 0;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; background: linear-gradient(to right, #fff, #a5b4fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Community Radio Database</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem;">
            The open-source collection of radio streams. Login to contribute stations, report broken links, and fix metadata.
        </p>
        <a href="/auth.php" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">Start Contributing</a>
    </div>
    <?php endif; ?>

    <?php if ($user): ?>
    <div class="dashboard-grid">
        
        <div class="card">
            <div class="tab-header">
                <button id="btn-add" onclick="toggleForm('add')" class="tab-btn active">Add Station</button>
                <button id="btn-report" onclick="toggleForm('report')" class="tab-btn">Report Issue</button>
            </div>

            <form id="form-add" action="/action.php" method="POST">
                <input type="hidden" name="action" value="add_radio">
                <div class="form-group">
                    <label>Station Name</label>
                    <input type="text" name="name" required placeholder="e.g. Neon City FM" class="form-control">
                </div>
                <div class="form-group">
                    <label>Stream URL</label>
                    <input type="url" name="stream_url" required placeholder="https://" class="form-control">
                </div>
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Country</label>
                        <input type="text" name="country" required placeholder="CZ" class="form-control" maxlength="2" style="text-transform: uppercase;">
                    </div>
                    <div style="flex: 2;">
                        <label>Genre</label>
                        <input type="text" name="genre" placeholder="Pop, Lo-Fi" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-4">Add to Queue</button>
            </form>

            <form id="form-report" action="/action.php" method="POST" style="display:none;">
                <input type="hidden" name="action" value="report_issue">
                <div class="form-group">
                    <label>Target Radio Name</label>
                    <input type="text" name="radio_name" id="report_radio_name" required placeholder="Click 'Report' on a radio card..." class="form-control">
                </div>
                <div class="form-group">
                    <label>Issue Type</label>
                    <select name="issue_type" class="form-control">
                        <option value="stream_not_working">Stream Offline</option>
                        <option value="wrong_info">Incorrect Metadata</option>
                        <option value="duplicate">Duplicate Station</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Details</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Describe what's wrong..."></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-block mt-4">Submit Report</button>
            </form>
        </div>

        <div class="card">
            <div class="flex-between mb-4">
                <h3 style="font-size: 1.1rem;">Pending Changes <span style="color:var(--text-muted); font-weight:400;">(<?php echo count($pendingChanges); ?>)</span></h3>
                <?php if (count($pendingChanges) > 0): ?>
                <form action="/action.php" method="POST">
                    <input type="hidden" name="action" value="submit_pr">
                    <button type="submit" class="btn btn-success btn-sm">Publish to GitHub</button>
                </form>
                <?php endif; ?>
            </div>
            
            <div class="changes-list">
                <?php if (empty($pendingChanges)): ?>
                    <div class="text-center" style="padding: 3rem 0; color: var(--text-muted);">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.3;">📭</div>
                        Your queue is empty.<br>Add a radio to get started.
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingChanges as $change): 
                        $data = json_decode($change['data'], true);
                    ?>
                    <div class="change-item">
                        <div class="change-info">
                            <?php if($change['change_type'] == 'add_radio'): ?>
                                <span class="badge badge-add">ADD</span>
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($data['name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($data['country']); ?></div>
                                </div>
                            <?php else: ?>
                                <span class="badge badge-issue">FIX</span>
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($data['radio_name']); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($data['issue_type']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="/action.php?action=delete&id=<?php echo $change['id']; ?>" class="delete-btn" title="Remove">&times;</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="radios-section">
        <div class="section-header">
            <div>
                <h2>Live Database</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Synced from GitHub Main Branch</p>
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
                <?php echo count($radios); ?> Stations
            </div>
        </div>
        
        <div class="radios-grid">
            <?php foreach ($radios as $radio): ?>
            <div class="radio-card">
                <div class="radio-content">
                    <div class="radio-header">
                        <h4><?php echo htmlspecialchars($radio['name']); ?></h4>
                    </div>
                    <div class="radio-body">
                        <span class="genre-tag"><?php echo htmlspecialchars($radio['genre'] ?? 'Radio'); ?></span>
                        <span class="flag"><?php echo htmlspecialchars($radio['country']); ?></span>
                    </div>
                </div>

                <div class="radio-overlay">
                    <a href="<?php echo htmlspecialchars($radio['stream_url']); ?>" target="_blank" class="btn btn-primary btn-sm btn-icon">
                        ▶ Play
                    </a>
                    <button onclick="prefillReport('<?php echo htmlspecialchars(addslashes($radio['name'])); ?>')" class="btn btn-danger btn-sm btn-icon">
                        ⚠️ Report
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
</body>
</html>