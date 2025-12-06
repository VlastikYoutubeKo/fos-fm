<?php
require_once 'config.php';
requireLogin();

$db = getDB();
$user = getCurrentUser();

// Get all pending changes
$stmt = $db->prepare("SELECT * FROM pending_changes WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$_SESSION['user_id']]);
$changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($changes)) {
    header('Location: /dashboard.php');
    exit;
}

$changeCount = count($changes);

// Prepare data for PR
$addRadios = [];
$reportIssues = [];

foreach ($changes as $change) {
    $data = json_decode($change['data'], true);
    if ($change['change_type'] === 'add_radio') {
        $addRadios[] = $data;
    } elseif ($change['change_type'] === 'report_issue') {
        $reportIssues[] = $data;
    }
}

// GitHub API setup
$repoOwner = GITHUB_REPO_OWNER;
$repoName = GITHUB_REPO_NAME;
$jsonFile = GITHUB_JSON_FILE;
$accessToken = $user['access_token'];

// 1. Get the default branch
$ch = curl_init("https://api.github.com/repos/$repoOwner/$repoName");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App',
    'Accept: application/vnd.github.v3+json'
]);
$repoResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("Failed to get repository information. HTTP Code: $httpCode");
}

$repoData = json_decode($repoResponse, true);
$defaultBranch = $repoData['default_branch'] ?? 'main';

// 2. Get the latest commit SHA of the default branch
$ch = curl_init("https://api.github.com/repos/$repoOwner/$repoName/git/ref/heads/$defaultBranch");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App'
]);
$refResponse = curl_exec($ch);
curl_close($ch);

$refData = json_decode($refResponse, true);
$baseSha = $refData['object']['sha'] ?? null;

if (!$baseSha) {
    die("Failed to get base branch SHA");
}

// 3. Create a new branch
$branchName = 'radio-update-' . time();
$ch = curl_init("https://api.github.com/repos/$repoOwner/$repoName/git/refs");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'ref' => "refs/heads/$branchName",
    'sha' => $baseSha
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App',
    'Content-Type: application/json'
]);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 201) {
    die("Failed to create branch. HTTP Code: $httpCode");
}

// 4. Get current radios.json content
$ch = curl_init("https://api.github.com/repos/$repoOwner/$repoName/contents/$jsonFile?ref=$defaultBranch");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App'
]);
$fileResponse = curl_exec($ch);
curl_close($ch);

$fileData = json_decode($fileResponse, true);
$currentRadios = [];

if (isset($fileData['content'])) {
    $decodedContent = base64_decode($fileData['content']);
    $currentRadios = json_decode($decodedContent, true) ?? [];
}

// 5. Merge new radios
foreach ($addRadios as $radio) {
    $currentRadios[] = [
        'name' => $radio['name'],
        'stream_url' => $radio['stream_url'],
        'url' => $radio['url'] ?? '',
        'country' => $radio['country'],
        'region' => $radio['region'] ?? '',
        'genre' => $radio['genre'] ?? ''
    ];
}

// 6. Create updated content
$updatedContent = json_encode($currentRadios, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

// 7. Update file in new branch
$ch = curl_init("https://api.github.com/repos/$repoOwner/$repoName/contents/$jsonFile");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'message' => "Add " . count($addRadios) . " radio stations and " . count($reportIssues) . " issue reports",
    'content' => base64_encode($updatedContent),
    'branch' => $branchName,
    'sha' => $fileData['sha'] ?? null
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App',
    'Content-Type: application/json'
]);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 && $httpCode !== 201) {
    die("Failed to update file. HTTP Code: $httpCode");
}

// 8. Create pull request
$prBody = "## Changes Summary\n\n";
$prBody .= "**Added Radios:** " . count($addRadios) . "\n";
$prBody .= "**Reported Issues:** " . count($reportIssues) . "\n\n";

if (!empty($addRadios)) {
    $prBody .= "### Added Radios\n\n";
    foreach ($addRadios as $radio) {
        $prBody .= "- **{$radio['name']}** ({$radio['country']}";
        if (!empty($radio['region'])) $prBody .= " - {$radio['region']}";
        $prBody .= ")\n";
        $prBody .= "  - Stream: {$radio['stream_url']}\n";
        if (!empty($radio['genre'])) $prBody .= "  - Genre: {$radio['genre']}\n";
    }
}

if (!empty($reportIssues)) {
    $prBody .= "\n### Reported Issues\n\n";
    foreach ($reportIssues as $issue) {
        $prBody .= "- **{$issue['radio_name']}**: " . str_replace('_', ' ', ucwords($issue['issue_type'], '_')) . "\n";
        if (!empty($issue['description'])) {
            $prBody .= "  > {$issue['description']}\n";
        }
    }
}

$ch = curl_init("https://api.github.com/repos/$repoOwner/$repoName/pulls");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'title' => "Radio Database Update - $changeCount changes",
    'body' => $prBody,
    'head' => $branchName,
    'base' => $defaultBranch
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'User-Agent: Radio-Submit-App',
    'Content-Type: application/json'
]);
$prResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 201) {
    die("Failed to create pull request. HTTP Code: $httpCode");
}

$prData = json_decode($prResponse, true);
$prNumber = $prData['number'];
$prUrl = $prData['html_url'];

// 9. Save PR to database
$stmt = $db->prepare("INSERT INTO submitted_prs (user_id, pr_number, pr_url, changes_count) VALUES (?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $prNumber, $prUrl, $changeCount]);

// 10. Delete pending changes
$stmt = $db->prepare("DELETE FROM pending_changes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);

// Redirect to success page
header("Location: /success.php?pr=$prNumber&url=" . urlencode($prUrl));
exit;
