<?php
// Ensure this path points to your actual config file
require_once 'includes/config.php';

if (!isLoggedIn()) { header('Location: /'); exit; }

$db = getDB();
$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

// --- 1. ADD RADIO ---
if ($action === 'add_radio' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation could go here
    $data = json_encode([
        'name' => trim($_POST['name']),
        'stream_url' => trim($_POST['stream_url']),
        'country' => strtoupper(trim($_POST['country'])),
        'genre' => trim($_POST['genre'] ?? '')
    ]);
    
    $stmt = $db->prepare("INSERT INTO pending_changes (user_id, change_type, data) VALUES (?, 'add_radio', ?)");
    $stmt->execute([$user_id, $data]);
}

// --- 2. REPORT ISSUE ---
if ($action === 'report_issue' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_encode([
        'radio_name' => trim($_POST['radio_name']),
        'issue_type' => $_POST['issue_type'],
        'description' => trim($_POST['description'] ?? '')
    ]);
    
    $stmt = $db->prepare("INSERT INTO pending_changes (user_id, change_type, data) VALUES (?, 'report_issue', ?)");
    $stmt->execute([$user_id, $data]);
}

// --- 3. DELETE PENDING CHANGE ---
if ($action === 'delete') {
    $id = $_GET['id'] ?? 0;
    $stmt = $db->prepare("DELETE FROM pending_changes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

// --- 4. SUBMIT TO GITHUB (The Big Logic) ---
if ($action === 'submit_pr') {
    // A. Fetch Pending Changes
    $stmt = $db->prepare("SELECT * FROM pending_changes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($changes)) { header('Location: /'); exit; }

    // B. Sort Data
    $addRadios = [];
    $reportIssues = [];
    foreach ($changes as $change) {
        $d = json_decode($change['data'], true);
        if ($change['change_type'] === 'add_radio') $addRadios[] = $d;
        else $reportIssues[] = $d;
    }

    // C. Get GitHub Access Token
    $stmt = $db->prepare("SELECT access_token FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $token = $stmt->fetchColumn();

    if (!$token) die("Error: No GitHub token found. Please logout and login again.");

    // D. Helper Function for GitHub API
    function apiReq($url, $headers, $post = null, $method = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
            if (!$method) curl_setopt($ch, CURLOPT_POST, true);
        }
        if ($method) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            die("GitHub API Error ($httpCode): " . ($decoded['message'] ?? $response));
        }
        return $decoded;
    }

    $api = "https://api.github.com/repos/" . GITHUB_REPO_OWNER . "/" . GITHUB_REPO_NAME;
    $headers = [
        "Authorization: Bearer $token",
        "User-Agent: " . APP_NAME,
        "Accept: application/vnd.github.v3+json"
    ];

    // E. Get Repository Info (to find default branch)
    $repoInfo = apiReq($api, $headers);
    $baseBranch = $repoInfo['default_branch'] ?? 'main';

    // F. Get SHA of Base Branch
    $refInfo = apiReq("$api/git/ref/heads/$baseBranch", $headers);
    $baseSha = $refInfo['object']['sha'];

    // G. Create New Branch
    $newBranch = 'update-' . time();
    apiReq("$api/git/refs", $headers, ["ref" => "refs/heads/$newBranch", "sha" => $baseSha]);

    // H. Get current radios.json content
    // Note: This assumes radios.json exists. If it might not, you'd need a try/catch here.
    $fileData = apiReq("$api/contents/" . GITHUB_JSON_FILE . "?ref=$baseBranch", $headers);
    $currentRadios = [];
    if (isset($fileData['content'])) {
        $currentRadios = json_decode(base64_decode($fileData['content']), true) ?? [];
    }

    // I. Append New Radios
    foreach ($addRadios as $r) {
        $currentRadios[] = [
            'name' => $r['name'],
            'stream_url' => $r['stream_url'],
            'country' => $r['country'],
            'genre' => $r['genre'] ?? ''
        ];
    }

    // J. Update radios.json in the new branch
    $newContent = base64_encode(json_encode($currentRadios, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    apiReq("$api/contents/" . GITHUB_JSON_FILE, $headers, [
        "message" => "Update radios: +" . count($addRadios) . " entries",
        "content" => $newContent,
        "branch" => $newBranch,
        "sha" => $fileData['sha']
    ], "PUT");

    // K. Create Pull Request
    $body = "## Changes Summary\n\n";
    if (!empty($addRadios)) {
        $body .= "### ? Added Radios (" . count($addRadios) . ")\n";
        foreach ($addRadios as $r) {
            $body .= "- **{$r['name']}** ({$r['country']})\n  - Stream: `{$r['stream_url']}`\n";
        }
    }
    if (!empty($reportIssues)) {
        $body .= "\n### ?? Reported Issues (" . count($reportIssues) . ")\n";
        foreach ($reportIssues as $i) {
            $body .= "- **{$i['radio_name']}**: " . str_replace('_', ' ', $i['issue_type']) . "\n";
            if (!empty($i['description'])) $body .= "  > {$i['description']}\n";
        }
    }

    $pr = apiReq("$api/pulls", $headers, [
        "title" => "Radio Database Update (" . count($changes) . " changes)",
        "body" => $body,
        "head" => $newBranch,
        "base" => $baseBranch
    ]);

    // L. Save PR to Database (CRITICAL FOR WEBHOOK)
    if (isset($pr['number'])) {
        $stmt = $db->prepare("INSERT INTO submitted_prs (user_id, pr_number, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$user_id, $pr['number']]);
    }

    // M. Clear Pending Changes
    $stmt = $db->prepare("DELETE FROM pending_changes WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Redirect to success
    header('Location: /index.php?status=pr_created&url=' . urlencode($pr['html_url']));
    exit;
}

// Fallback redirect
header('Location: /index.php');