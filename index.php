<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get all blog posts
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT bp.*, u.username 
    FROM blog_post bp 
    JOIN user u ON bp.user_id = u.id 
    ORDER BY bp.created_at DESC
");
$stmt->execute();
$blogs = $stmt->fetchAll();

$currentUser = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo"><?php echo APP_NAME; ?></a>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span class="user-info">Hello, <?php echo $currentUser['username']; ?></span>
                    <a href="create-blog.php" class="btn btn-primary">Create Blog</a>
                    <a href="api/logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container main-content">
        <h1 class="page-title">Latest Blog Posts</h1>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <div class="blog-grid">
            <?php if (empty($blogs)): ?>
                <div class="empty-state">
                    <p>No blog posts yet. Be the first to create one!</p>
                    <?php if (isLoggedIn()): ?>
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
                            <span class="author">By <?php echo htmlspecialchars($blog['username']); ?></span>
                            <span class="date"><?php echo formatDate($blog['created_at']); ?></span>
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
</body>
</html>