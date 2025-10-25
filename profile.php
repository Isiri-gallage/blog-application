<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get user ID from URL or use current user
$userId = isset($_GET['id']) ? intval($_GET['id']) : getCurrentUserId();

if (!$userId) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Get user info with profile picture
$stmt = $conn->prepare("SELECT id, username, email, bio, profile_picture, created_at FROM user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit();
}

// Get user's blogs
$stmt = $conn->prepare("
    SELECT bp.*, 
           (SELECT COUNT(*) FROM blog_like WHERE blog_id = bp.id) as likes,
           (SELECT COUNT(*) FROM comment WHERE blog_id = bp.id) as comments,
           COALESCE(bp.views, 0) as views
    FROM blog_post bp 
    WHERE bp.user_id = ? 
    ORDER BY bp.created_at DESC
");
$stmt->execute([$userId]);
$blogs = $stmt->fetchAll();

// Get stats
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM blog_post WHERE user_id = ?");
$stmt->execute([$userId]);
$totalBlogs = $stmt->fetch()['total'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM blog_like bl 
    JOIN blog_post bp ON bl.blog_id = bp.id 
    WHERE bp.user_id = ?
");
$stmt->execute([$userId]);
$totalLikes = $stmt->fetch()['total'];

$isOwnProfile = isLoggedIn() && getCurrentUserId() == $userId;
$currentUser = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="brand-section">
                <a href="index.php" class="logo"><?php echo APP_NAME; ?></a>
                <p class="brand-tagline">Where ideas come to life and stories find their voice</p>
            </div>
            
            <div class="nav-search">
                <form method="GET" action="index.php" class="nav-search-form">
                    <input type="text" name="search" placeholder="Search blogs..." class="nav-search-input">
                    <button type="submit" class="nav-search-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>
            
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="create-blog.php" class="btn btn-primary">Create Blog</a>
                    
                    <div class="profile-dropdown">
                        <button class="profile-trigger" onclick="toggleProfileMenu()">
                            <?php if (isset($currentUser['profile_picture']) && $currentUser['profile_picture'] && file_exists($currentUser['profile_picture'])): ?>
                                <img src="<?php echo $currentUser['profile_picture']; ?>" alt="<?php echo htmlspecialchars($currentUser['username']); ?>" class="profile-avatar-small">
                            <?php else: ?>
                                <div class="profile-avatar-small">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <span class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        
                        <div class="profile-dropdown-menu" id="profileMenu">
                            <a href="profile.php" class="dropdown-item">
                                <span class="item-icon">👤</span>
                                <span>My Profile</span>
                            </a>
                            <a href="api/logout.php" class="dropdown-item">
                                <span class="item-icon">🚪</span>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container main-content">
        <a href="index.php" class="back-link">← Back to all posts</a>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-header">
            <div class="profile-avatar">
                <?php if (isset($user['profile_picture']) && !empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" class="avatar-image">
                <?php else: ?>
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <?php if (!empty($user['bio'])): ?>
                    <p class="profile-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo $totalBlogs; ?></span>
                        <span class="stat-label">Blogs</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo $totalLikes; ?></span>
                        <span class="stat-label">Likes</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo formatDate($user['created_at']); ?></span>
                        <span class="stat-label">Joined</span>
                    </div>
                </div>
                <?php if ($isOwnProfile): ?>
                    <a href="edit-profile.php" class="btn btn-primary">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>
        
        <h2 class="section-title"><?php echo $isOwnProfile ? 'My Blogs' : 'Blogs by ' . htmlspecialchars($user['username']); ?></h2>
        
        <div class="blog-grid">
            <?php if (empty($blogs)): ?>
                <div class="empty-state">
                    <p><?php echo $isOwnProfile ? "You haven't created any blogs yet." : "This user hasn't created any blogs yet."; ?></p>
                    <?php if ($isOwnProfile): ?>
                        <a href="create-blog.php" class="btn btn-primary">Create Your First Blog</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="blog-card">
                        <h2 class="blog-title">
                            <a href="view-blog.php?id=<?php echo $blog['id']; ?>">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </a>
                        </h2>
                        <div class="blog-meta">
                            <span class="date"><?php echo formatDate($blog['created_at']); ?></span>
                            <span class="likes">❤️ <?php echo $blog['likes']; ?></span>
                            <span class="comments">💬 <?php echo $blog['comments']; ?></span>
                            <span class="views">👁️ <?php echo $blog['views']; ?></span>
                        </div>
                        <div class="blog-excerpt">
                            <?php echo truncateText(strip_tags($blog['content']), 150); ?>
                        </div>
                        <a href="view-blog.php?id=<?php echo $blog['id']; ?>" class="read-more">Read More →</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <script>
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('show');
        }
        
        window.onclick = function(event) {
            if (!event.target.matches('.profile-trigger') && !event.target.closest('.profile-trigger')) {
                const menu = document.getElementById('profileMenu');
                if (menu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>