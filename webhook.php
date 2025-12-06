<?php
// Ensure path is correct
require_once 'includes/config.php';

// 1. Get Payload directly
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// 2. Check if it's a "closed" Pull Request event
if (($data['action'] ?? '') === 'closed' && isset($data['pull_request'])) {
    
    $prNumber = $data['pull_request']['number'];
    $isMerged = $data['pull_request']['merged']; // true if merged, false if closed without merge
    
    $status = $isMerged ? 'merged' : 'closed';
    
    // 3. Update Database
    $db = getDB();
    $stmt = $db->prepare("UPDATE submitted_prs SET status = ? WHERE pr_number = ?");
    $stmt->execute([$status, $prNumber]);
    
    // 4. Respond to GitHub
    http_response_code(200);
    echo "PR #$prNumber updated to $status";
} else {
    // Just say OK to other events (like 'opened') so GitHub doesn't complain
    echo "Event ignored";
}