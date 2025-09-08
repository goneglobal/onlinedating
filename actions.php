<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$currentUser = getCurrentUser();
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $targetUserId = (int)$_POST['user_id'];
    
    if ($action === 'like') {
        // Add like
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO likes (liker_id, liked_id) VALUES (?, ?)");
            $stmt->execute([$currentUser['id'], $targetUserId]);
            
            // Check if it's a mutual like (match)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE liker_id = ? AND liked_id = ?");
            $stmt->execute([$targetUserId, $currentUser['id']]);
            
            if ($stmt->fetchColumn() > 0) {
                // It's a match! Create match record
                $stmt = $pdo->prepare("INSERT IGNORE INTO matches (user1_id, user2_id, status) VALUES (?, ?, 'matched')");
                $stmt->execute([min($currentUser['id'], $targetUserId), max($currentUser['id'], $targetUserId)]);
                
                echo json_encode(['success' => true, 'match' => true, 'message' => 'It\'s a match! 🎉']);
            } else {
                echo json_encode(['success' => true, 'match' => false, 'message' => 'Like sent!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to send like']);
        }
        
    } elseif ($action === 'reject') {
        // Add rejection
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO matches (user1_id, user2_id, status) VALUES (?, ?, 'rejected')");
            $stmt->execute([min($currentUser['id'], $targetUserId), max($currentUser['id'], $targetUserId)]);
            
            echo json_encode(['success' => true, 'message' => 'Passed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to process rejection']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>