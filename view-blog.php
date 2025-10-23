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

// Increment view count
$stmt = $conn->prepare("UPDATE blog_post SET views = views + 1 WHERE id = ?");
$stmt->execute([$blogId]);

// Get likes count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM blog_like WHERE blog_id = ?");
$stmt->execute([$blogId]);
$likesCount = $stmt->fetch()['total'];

// Check if current user liked this blog
$userLiked = false;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT id FROM blog_like WHERE blog_id = ? AND user_id = ?");
    $stmt->execute([$blogId, getCurrentUserId()]);
    $userLiked = $stmt->fetch() ? true : false;
}

// Get comments
$stmt = $conn->prepare("
    SELECT c.*, u.username 
    FROM comment c 
    JOIN user u ON c.user_id = u.id 
    WHERE c.blog_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$blogId]);
$comments = $stmt->fetchAll();

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
                    <a href="profile.php" class="btn btn-secondary">My Profile</a>
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
                    <span class="author">
                        By <a href="profile.php?id=<?php echo $blog['user_id']; ?>"><?php echo htmlspecialchars($blog['username']); ?></a>
                    </span>
                    <span class="date">Published on <?php echo formatDateTime($blog['created_at']); ?></span>
                    <?php if ($blog['updated_at'] != $blog['created_at']): ?>
                        <span class="updated">Updated on <?php echo formatDateTime($blog['updated_at']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="blog-engagement">
                    <?php if (isLoggedIn()): ?>
                        <button onclick="toggleLike(<?php echo $blog['id']; ?>)" class="like-btn <?php echo $userLiked ? 'liked' : ''; ?>" id="likeBtn">
                            <span class="like-icon">❤️</span>
                            <span class="like-count" id="likeCount"><?php echo $likesCount; ?></span>
                        </button>
                    <?php else: ?>
                        <span class="like-display">❤️ <?php echo $likesCount; ?></span>
                    <?php endif; ?>
                    <span class="comment-count">💬 <?php echo count($comments); ?> Comments</span>
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
        
        <!-- Comments Section -->
        <div class="comments-section">
            <h2>Comments (<?php echo count($comments); ?>)</h2>
            
            <?php if (isLoggedIn()): ?>
                <form id="commentForm" class="comment-form">
                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                    <textarea name="content" placeholder="Write a comment..." rows="3" required></textarea>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
            <?php else: ?>
                <p class="login-prompt"><a href="login.php">Login</a> to post a comment</p>
            <?php endif; ?>
            
            <div id="commentsList" class="comments-list">
                <?php if (empty($comments)): ?>
                    <p class="no-comments">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="comment-author">
                                    <?php echo htmlspecialchars($comment['username']); ?>
                                </a>
                                <span class="comment-date"><?php echo formatDateTime($comment['created_at']); ?></span>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                            <?php if (isLoggedIn() && $comment['user_id'] == getCurrentUserId()): ?>
                                <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="comment-delete">Delete</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Toggle Like
        async function toggleLike(blogId) {
            try {
                const formData = new FormData();
                formData.append('blog_id', blogId);
                
                const response = await fetch('api/toggle-like.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const likeBtn = document.getElementById('likeBtn');
                    const likeCount = document.getElementById('likeCount');
                    
                    if (data.action === 'liked') {
                        likeBtn.classList.add('liked');
                    } else {
                        likeBtn.classList.remove('liked');
                    }
                    
                    likeCount.textContent = data.likes;
                } else {
                    alert(data.message || 'Failed to toggle like');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        }
        
        // Add Comment
        document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/add-comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Add comment to list
                    const commentsList = document.getElementById('commentsList');
                    const noComments = commentsList.querySelector('.no-comments');
                    if (noComments) {
                        noComments.remove();
                    }
                    
                    const comment = data.comment;
                    const commentHtml = `
                        <div class="comment" id="comment-${comment.id}">
                            <div class="comment-header">
                                <a href="profile.php?id=${comment.user_id}" class="comment-author">
                                    ${escapeHtml(comment.username)}
                                </a>
                                <span class="comment-date">${formatDateTime(comment.created_at)}</span>
                            </div>
                            <div class="comment-content">
                                ${escapeHtml(comment.content).replace(/\n/g, '<br>')}
                            </div>
                            <button onclick="deleteComment(${comment.id})" class="comment-delete">Delete</button>
                        </div>
                    `;
                    
                    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                    
                    // Clear form
                    e.target.reset();
                    
                    // Update comment count
                    const countElement = document.querySelector('.comment-count');
                    if (countElement) {
                        const currentCount = parseInt(countElement.textContent.match(/\d+/)[0]);
                        countElement.textContent = `💬 ${currentCount + 1} Comments`;
                    }
                    
                    const titleCount = document.querySelector('.comments-section h2');
                    if (titleCount) {
                        const currentCount = parseInt(titleCount.textContent.match(/\d+/)[0]);
                        titleCount.textContent = `Comments (${currentCount + 1})`;
                    }
                } else {
                    alert(data.message || 'Failed to add comment');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        });
        
        // Delete Comment
        async function deleteComment(commentId) {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('id', commentId);
                
                const response = await fetch('api/delete-comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById(`comment-${commentId}`).remove();
                    
                    // Update comment count
                    const countElement = document.querySelector('.comment-count');
                    if (countElement) {
                        const currentCount = parseInt(countElement.textContent.match(/\d+/)[0]);
                        countElement.textContent = `💬 ${currentCount - 1} Comments`;
                    }
                    
                    const titleCount = document.querySelector('.comments-section h2');
                    if (titleCount) {
                        const currentCount = parseInt(titleCount.textContent.match(/\d+/)[0]);
                        titleCount.textContent = `Comments (${currentCount - 1})`;
                    }
                    
                    // Show "no comments" if empty
                    const commentsList = document.getElementById('commentsList');
                    if (commentsList.children.length === 0) {
                        commentsList.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
                    }
                } else {
                    alert(data.message || 'Failed to delete comment');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        }
        
        // Delete Blog
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
        
        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatDateTime(datetime) {
            const date = new Date(datetime);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>