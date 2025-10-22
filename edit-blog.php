<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

// Get blog ID
$blogId = $_GET['id'] ?? 0;

// Fetch blog post
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM blog_post WHERE id = ?");
$stmt->execute([$blogId]);
$blog = $stmt->fetch();

// Check if blog exists and user owns it
if (!$blog || $blog['user_id'] != getCurrentUserId()) {
    header('Location: index.php');
    exit();
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo"><?php echo APP_NAME; ?></a>
            <div class="nav-links">
                <span class="user-info">Hello, <?php echo $currentUser['username']; ?></span>
                <a href="profile.php" class="btn btn-secondary">My Profile</a>
                <a href="index.php" class="btn btn-secondary">Home</a>
                <a href="api/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container main-content">
        <h1 class="page-title">Edit Blog Post</h1>
        
        <form id="editBlogForm" class="blog-form">
            <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($blog['title']); ?>">
            </div>
            
            <div class="form-group">
                <label for="content">Content (Markdown supported)</label>
                <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Blog</button>
                <a href="view-blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
        
        <div class="markdown-preview">
            <h3>Preview</h3>
            <div id="preview" class="preview-content"></div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Live preview
        const contentField = document.getElementById('content');
        const updatePreview = () => {
            const markdown = contentField.value;
            const html = markdownToHtml(markdown);
            document.getElementById('preview').innerHTML = html;
        };
        
        // Initial preview
        updatePreview();
        
        contentField.addEventListener('input', updatePreview);
        
        // Form submission
        document.getElementById('editBlogForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/update-blog.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'view-blog.php?id=' + formData.get('id');
                } else {
                    alert(data.message || 'Failed to update blog');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        });
    </script>
</body>
</html>