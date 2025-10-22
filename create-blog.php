<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog - <?php echo APP_NAME; ?></title>
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
        <h1 class="page-title">Create New Blog Post</h1>
        
        <form id="createBlogForm" class="blog-form">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required placeholder="Enter blog title">
            </div>
            
            <div class="form-group">
                <label for="content">Content (Markdown supported)</label>
                <textarea id="content" name="content" rows="15" required placeholder="Write your blog content here. You can use Markdown formatting:

# Heading 1
## Heading 2
### Heading 3

**bold text**
*italic text*

[link text](url)"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Publish Blog</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
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
        document.getElementById('content').addEventListener('input', (e) => {
            const markdown = e.target.value;
            const html = markdownToHtml(markdown);
            document.getElementById('preview').innerHTML = html;
        });
        
        // Form submission
        document.getElementById('createBlogForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/create-blog.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'view-blog.php?id=' + data.blog_id;
                } else {
                    alert(data.message || 'Failed to create blog');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        });
    </script>
</body>
</html>