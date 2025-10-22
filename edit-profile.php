<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$conn = getDBConnection();
$currentUser = getCurrentUser();

// Get user with bio
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([getCurrentUserId()]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if (empty($username)) {
        setFlashMessage('error', 'Username is required');
    } else {
        // Check if username is taken by another user
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? AND id != ?");
        $stmt->execute([$username, getCurrentUserId()]);
        
        if ($stmt->fetch()) {
            setFlashMessage('error', 'Username already taken');
        } else {
            try {
                $stmt = $conn->prepare("UPDATE user SET username = ?, bio = ? WHERE id = ?");
                $stmt->execute([$username, $bio, getCurrentUserId()]);
                
                $_SESSION['username'] = $username;
                setFlashMessage('success', 'Profile updated successfully');
                header('Location: profile.php');
                exit();
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to update profile');
            }
        }
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo"><?php echo APP_NAME; ?></a>
            <div class="nav-links">
                <span class="user-info">Hello, <?php echo $currentUser['username']; ?></span>
                <a href="profile.php" class="btn btn-secondary">My Profile</a>
                <a href="api/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container main-content">
        <h1 class="page-title">Edit Profile</h1>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="blog-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email (cannot be changed)</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="5" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
</body>
</html>