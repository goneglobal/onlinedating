<?php
require_once 'config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirectTo('login.php');
}

$currentUser = getCurrentUser();
$pdo = getDBConnection();

// Get potential matches (users of opposite gender who haven't been liked/rejected)
$stmt = $pdo->prepare("
    SELECT u.* FROM users u 
    WHERE u.id != ? 
    AND u.id NOT IN (
        SELECT liked_id FROM likes WHERE liker_id = ?
    )
    AND u.id NOT IN (
        SELECT user2_id FROM matches WHERE user1_id = ? AND status = 'rejected'
    )
    ORDER BY RAND() 
    LIMIT 5
");
$stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
$potentialMatches = $stmt->fetchAll();

// Get recent matches
$stmt = $pdo->prepare("
    SELECT u.*, m.created_at as match_date 
    FROM users u 
    JOIN matches m ON (u.id = m.user1_id OR u.id = m.user2_id) 
    WHERE (m.user1_id = ? OR m.user2_id = ?) 
    AND u.id != ? 
    AND m.status = 'matched'
    ORDER BY m.created_at DESC 
    LIMIT 5
");
$stmt->execute([$currentUser['id'], $currentUser['id'], $currentUser['id']]);
$recentMatches = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Dating - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">💕 LoveConnect</h1>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="matches.php" class="nav-link">Matches</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h2>Welcome back, <?php echo sanitizeInput($currentUser['first_name']); ?>!</h2>
            <p>Find your perfect match today</p>
        </div>

        <div class="main-content">
            <div class="discover-section">
                <h3>Discover New People</h3>
                <?php if (empty($potentialMatches)): ?>
                    <p>No new profiles to show. Check back later!</p>
                <?php else: ?>
                    <div class="cards-container">
                        <?php foreach ($potentialMatches as $user): ?>
                            <div class="profile-card" data-user-id="<?php echo $user['id']; ?>">
                                <div class="profile-image">
                                    <?php if ($user['profile_picture']): ?>
                                        <img src="<?php echo sanitizeInput($user['profile_picture']); ?>" alt="Profile">
                                    <?php else: ?>
                                        <div class="placeholder-image">📷</div>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-info">
                                    <h4><?php echo sanitizeInput($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                    <p class="age">Age: <?php echo $user['age']; ?></p>
                                    <p class="location">📍 <?php echo sanitizeInput($user['location']); ?></p>
                                    <p class="bio"><?php echo sanitizeInput($user['bio']); ?></p>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-reject" onclick="handleAction(<?php echo $user['id']; ?>, 'reject')">❌</button>
                                    <button class="btn-like" onclick="handleAction(<?php echo $user['id']; ?>, 'like')">❤️</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="matches-section">
                <h3>Recent Matches</h3>
                <?php if (empty($recentMatches)): ?>
                    <p>No matches yet. Start liking profiles!</p>
                <?php else: ?>
                    <div class="matches-list">
                        <?php foreach ($recentMatches as $match): ?>
                            <div class="match-item">
                                <div class="match-avatar">
                                    <?php if ($match['profile_picture']): ?>
                                        <img src="<?php echo sanitizeInput($match['profile_picture']); ?>" alt="Match">
                                    <?php else: ?>
                                        <div class="placeholder-avatar">👤</div>
                                    <?php endif; ?>
                                </div>
                                <div class="match-info">
                                    <h5><?php echo sanitizeInput($match['first_name']); ?></h5>
                                    <p>Matched <?php echo date('M j', strtotime($match['match_date'])); ?></p>
                                </div>
                                <a href="messages.php?user=<?php echo $match['id']; ?>" class="btn-message">💬</a>
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