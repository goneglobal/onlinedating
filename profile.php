<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirectTo('login.php');
}

$currentUser = getCurrentUser();
$pdo = getDBConnection();
$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $age = (int)$_POST['age'];
    $gender = sanitizeInput($_POST['gender']);
    $bio = sanitizeInput($_POST['bio']);
    $location = sanitizeInput($_POST['location']);
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($age) || empty($gender)) {
        $message = '<div class="error-message">Please fill in all required fields.</div>';
    } elseif ($age < 18 || $age > 100) {
        $message = '<div class="error-message">Age must be between 18 and 100.</div>';
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $message = '<div class="error-message">Please select a valid gender.</div>';
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, age = ?, gender = ?, bio = ?, location = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$firstName, $lastName, $age, $gender, $bio, $location, $currentUser['id']])) {
            $message = '<div class="success-message">Profile updated successfully!</div>';
            // Refresh user data
            $currentUser = getCurrentUser();
        } else {
            $message = '<div class="error-message">Failed to update profile. Please try again.</div>';
        }
    }
}

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_likes FROM likes WHERE liker_id = ?");
$stmt->execute([$currentUser['id']]);
$totalLikes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_matches FROM matches WHERE (user1_id = ? OR user2_id = ?) AND status = 'matched'");
$stmt->execute([$currentUser['id'], $currentUser['id']]);
$totalMatches = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_messages FROM messages WHERE sender_id = ?");
$stmt->execute([$currentUser['id']]);
$totalMessages = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Dating - Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">💕 LoveConnect</h1>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="matches.php" class="nav-link">Matches</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if ($currentUser['profile_picture']): ?>
                        <img src="<?php echo sanitizeInput($currentUser['profile_picture']); ?>" alt="Profile">
                    <?php else: ?>
                        <div class="placeholder-avatar large">👤</div>
                    <?php endif; ?>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $totalLikes; ?></span>
                        <span class="stat-label">Likes Given</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $totalMatches; ?></span>
                        <span class="stat-label">Matches</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $totalMessages; ?></span>
                        <span class="stat-label">Messages Sent</span>
                    </div>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="profile-form">
                <h2>Edit Profile</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first-name">First Name:</label>
                            <input type="text" id="first-name" name="first_name" value="<?php echo sanitizeInput($currentUser['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last-name">Last Name:</label>
                            <input type="text" id="last-name" name="last_name" value="<?php echo sanitizeInput($currentUser['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">Age:</label>
                            <input type="number" id="age" name="age" value="<?php echo $currentUser['age']; ?>" min="18" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="male" <?php echo $currentUser['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $currentUser['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $currentUser['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" value="<?php echo sanitizeInput($currentUser['location']); ?>" placeholder="City, State">
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio:</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo sanitizeInput($currentUser['bio']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Account Information:</label>
                        <div class="readonly-info">
                            <p><strong>Username:</strong> <?php echo sanitizeInput($currentUser['username']); ?></p>
                            <p><strong>Email:</strong> <?php echo sanitizeInput($currentUser['email']); ?></p>
                            <p><strong>Member since:</strong> <?php echo date('F Y', strtotime($currentUser['created_at'])); ?></p>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>