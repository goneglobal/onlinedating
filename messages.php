<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$currentUser = getCurrentUser();
$pdo = getDBConnection();

$selectedUserId = isset($_GET['user']) ? (int)$_GET['user'] : null;
$message = '';

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiverId = (int)$_POST['receiver_id'];
    $messageText = sanitizeInput($_POST['message']);
    
    if (empty($messageText)) {
        $message = '<div class="error-message">Please enter a message.</div>';
    } else {
        // Verify that users are matched
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM matches 
            WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)) 
            AND status = 'matched'
        ");
        $stmt->execute([$currentUser['id'], $receiverId, $receiverId, $currentUser['id']]);
        
        if ($stmt->fetchColumn() > 0) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            if ($stmt->execute([$currentUser['id'], $receiverId, $messageText])) {
                $message = '<div class="success-message">Message sent!</div>';
            } else {
                $message = '<div class="error-message">Failed to send message.</div>';
            }
        } else {
            $message = '<div class="error-message">You can only message your matches.</div>';
        }
    }
}

// Get all conversations
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        u.id, u.first_name, u.last_name, u.profile_picture,
        (SELECT message FROM messages 
         WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
         ORDER BY sent_at DESC LIMIT 1) as last_message,
        (SELECT sent_at FROM messages 
         WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
         ORDER BY sent_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = u.id AND receiver_id = ? AND is_read = FALSE) as unread_count
    FROM users u
    WHERE u.id IN (
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as contact_id
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
    )
    ORDER BY last_message_time DESC
");
$stmt->execute([
    $currentUser['id'], $currentUser['id'], 
    $currentUser['id'], $currentUser['id'], 
    $currentUser['id'], 
    $currentUser['id'], 
    $currentUser['id'], $currentUser['id']
]);
$conversations = $stmt->fetchAll();

// Get messages for selected conversation
$messages = [];
$selectedUser = null;
if ($selectedUserId) {
    // Get selected user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch();
    
    if ($selectedUser) {
        // Get messages
        $stmt = $pdo->prepare("
            SELECT m.*, u.first_name, u.profile_picture 
            FROM messages m 
            JOIN users u ON m.sender_id = u.id 
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
        ");
        $stmt->execute([$currentUser['id'], $selectedUserId, $selectedUserId, $currentUser['id']]);
        $messages = $stmt->fetchAll();
        
        // Mark messages as read
        $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE sender_id = ? AND receiver_id = ?");
        $stmt->execute([$selectedUserId, $currentUser['id']]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Dating - Messages</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">💕 LoveConnect</h1>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="matches.php" class="nav-link">Matches</a>
                <a href="messages.php" class="nav-link active">Messages</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="messages-container">
            <div class="conversations-list">
                <h3>Conversations</h3>
                <?php if (empty($conversations)): ?>
                    <div class="empty-conversations">
                        <p>No conversations yet.</p>
                        <a href="matches.php" class="btn-primary">View Matches</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item <?php echo $selectedUserId == $conv['id'] ? 'active' : ''; ?>">
                            <a href="messages.php?user=<?php echo $conv['id']; ?>">
                                <div class="conv-avatar">
                                    <?php if ($conv['profile_picture']): ?>
                                        <img src="<?php echo sanitizeInput($conv['profile_picture']); ?>" alt="Profile">
                                    <?php else: ?>
                                        <div class="placeholder-avatar">👤</div>
                                    <?php endif; ?>
                                </div>
                                <div class="conv-info">
                                    <h5><?php echo sanitizeInput($conv['first_name'] . ' ' . $conv['last_name']); ?></h5>
                                    <p class="last-message"><?php echo sanitizeInput($conv['last_message']); ?></p>
                                    <span class="message-time"><?php echo date('M j', strtotime($conv['last_message_time'])); ?></span>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <div class="unread-badge"><?php echo $conv['unread_count']; ?></div>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-area">
                <?php if (!$selectedUser): ?>
                    <div class="no-chat-selected">
                        <div class="empty-icon">💬</div>
                        <h3>Select a conversation</h3>
                        <p>Choose a conversation from the left to start messaging</p>
                    </div>
                <?php else: ?>
                    <div class="chat-header">
                        <div class="chat-user-info">
                            <div class="chat-avatar">
                                <?php if ($selectedUser['profile_picture']): ?>
                                    <img src="<?php echo sanitizeInput($selectedUser['profile_picture']); ?>" alt="Profile">
                                <?php else: ?>
                                    <div class="placeholder-avatar">👤</div>
                                <?php endif; ?>
                            </div>
                            <h4><?php echo sanitizeInput($selectedUser['first_name'] . ' ' . $selectedUser['last_name']); ?></h4>
                        </div>
                    </div>

                    <div class="messages-area" id="messagesArea">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $currentUser['id'] ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <p><?php echo sanitizeInput($msg['message']); ?></p>
                                    <span class="message-time"><?php echo date('M j, g:i A', strtotime($msg['sent_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php echo $message; ?>

                    <form class="message-form" method="POST">
                        <input type="hidden" name="receiver_id" value="<?php echo $selectedUser['id']; ?>">
                        <div class="message-input-container">
                            <textarea name="message" placeholder="Type your message..." rows="2" required></textarea>
                            <button type="submit" name="send_message" class="btn-send">Send</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Auto-scroll to bottom of messages
        const messagesArea = document.getElementById('messagesArea');
        if (messagesArea) {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }
    </script>
</body>
</html>