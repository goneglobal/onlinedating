<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$currentUser = getCurrentUser();
$pdo = getDBConnection();

// Get all matches
$stmt = $pdo->prepare("
    SELECT u.*, m.created_at as match_date, m.status 
    FROM users u 
    JOIN matches m ON (u.id = m.user1_id OR u.id = m.user2_id) 
    WHERE (m.user1_id = ? OR m.user2_id = ?) 
    AND u.id != ? 
    AND m.status = 'matched'
    ORDER BY m.created_at DESC
");
$stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
$matches = $stmt->fetchAll();

// Get pending likes (people who liked you but you haven't responded)
$stmt = $pdo->prepare("
    SELECT u.*, l.created_at as like_date 
    FROM users u 
    JOIN likes l ON u.id = l.liker_id 
    WHERE l.liked_id = ? 
    AND l.liker_id NOT IN (
        SELECT liked_id FROM likes WHERE liker_id = ?
    )
    ORDER BY l.created_at DESC
");
$stmt->execute([$currentUser['id'], $currentUser['id']]);
$pendingLikes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Dating - Matches</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">💕 LoveConnect</h1>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="matches.php" class="nav-link active">Matches</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="matches-container">
            <!-- Pending Likes Section -->
            <?php if (!empty($pendingLikes)): ?>
                <div class="section">
                    <h2>People Who Liked You</h2>
                    <div class="pending-likes">
                        <?php foreach ($pendingLikes as $user): ?>
                            <div class="like-card" data-user-id="<?php echo $user['id']; ?>">
                                <div class="like-avatar">
                                    <?php if ($user['profile_picture']): ?>
                                        <img src="<?php echo sanitizeInput($user['profile_picture']); ?>" alt="Profile">
                                    <?php else: ?>
                                        <div class="placeholder-avatar">👤</div>
                                    <?php endif; ?>
                                </div>
                                <div class="like-info">
                                    <h4><?php echo sanitizeInput($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                    <p class="age">Age: <?php echo $user['age']; ?></p>
                                    <p class="location">📍 <?php echo sanitizeInput($user['location']); ?></p>
                                    <p class="bio"><?php echo sanitizeInput($user['bio']); ?></p>
                                    <p class="like-date">Liked you <?php echo date('M j, Y', strtotime($user['like_date'])); ?></p>
                                </div>
                                <div class="like-actions">
                                    <button class="btn-reject" onclick="handleAction(<?php echo $user['id']; ?>, 'reject')">❌ Pass</button>
                                    <button class="btn-like" onclick="handleAction(<?php echo $user['id']; ?>, 'like')">❤️ Like Back</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Matches Section -->
            <div class="section">
                <h2>Your Matches (<?php echo count($matches); ?>)</h2>
                <?php if (empty($matches)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">💔</div>
                        <h3>No matches yet</h3>
                        <p>Start liking profiles to find your matches!</p>
                        <a href="index.php" class="btn-primary">Discover People</a>
                    </div>
                <?php else: ?>
                    <div class="matches-grid">
                        <?php foreach ($matches as $match): ?>
                            <div class="match-card">
                                <div class="match-image">
                                    <?php if ($match['profile_picture']): ?>
                                        <img src="<?php echo sanitizeInput($match['profile_picture']); ?>" alt="Match">
                                    <?php else: ?>
                                        <div class="placeholder-image">📷</div>
                                    <?php endif; ?>
                                </div>
                                <div class="match-info">
                                    <h4><?php echo sanitizeInput($match['first_name'] . ' ' . $match['last_name']); ?></h4>
                                    <p class="age">Age: <?php echo $match['age']; ?></p>
                                    <p class="location">📍 <?php echo sanitizeInput($match['location']); ?></p>
                                    <p class="bio"><?php echo sanitizeInput($match['bio']); ?></p>
                                    <p class="match-date">Matched <?php echo date('M j, Y', strtotime($match['match_date'])); ?></p>
                                </div>
                                <div class="match-actions">
                                    <a href="messages.php?user=<?php echo $match['id']; ?>" class="btn-message">💬 Message</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>