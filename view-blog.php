<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get blog ID
$blogId = $_GET['id'] ?? 0;

// Fetch blog post with author info
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT bp.*, u.username 
    FROM blog_post bp 
    JOIN user u ON bp.user_id = u.id 
    WHERE bp.id = ?
");
$stmt->execute([$blogId]);
$blog = $stmt->fetch();

// Check if blog exists
if (!$blog) {
    header('Location: index.php');
    exit();
}

$currentUser = getCurrentUser();
$isOwner = isLoggedIn() && $blog['user_id'] == getCurrentUserId();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - <?php echo APP_NAME; ?></title>
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
        <a href="index.php" class="back-link">← Back to all posts</a>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <article class="blog-single">
            <header class="blog-header">
                <h1 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                <div class="blog-meta">
                    <span class="author">By <?php echo htmlspecialchars($blog['username']); ?></span>
                    <span class="date">Published on <?php echo formatDateTime($blog['created_at']); ?></span>
                    <?php if ($blog['updated_at'] != $blog['created_at']): ?>
                        <span class="updated">Updated on <?php echo formatDateTime($blog['updated_at']); ?></span>
                    <?php endif; ?>
                </div>
            </header>
            
            <div class="blog-content">
                <?php echo markdownToHtml($blog['content']); ?>
            </div>
            
            <?php if ($isOwner): ?>
                <div class="blog-actions">
                    <a href="edit-blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-primary">Edit Blog</a>
                    <button onclick="deleteBlog(<?php echo $blog['id']; ?>)" class="btn btn-danger">Delete Blog</button>
                </div>
            <?php endif; ?>
        </article>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <script>
        async function deleteBlog(id) {
            if (!confirm('Are you sure you want to delete this blog post? This action cannot be undone.')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch('api/delete-blog.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    alert(data.message || 'Failed to delete blog');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        }
    </script>
</body>
</html>