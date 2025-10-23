<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$conn = getDBConnection();

// Get search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get statistics
$stmt = $conn->query("SELECT COUNT(*) as total FROM blog_post");
$totalBlogs = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM user");
$totalUsers = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COALESCE(SUM(views), 0) as total FROM blog_post");
$totalViews = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM comment");
$totalComments = $stmt->fetch()['total'];

// Get all blog posts with search
if ($searchQuery) {
    $stmt = $conn->prepare("
        SELECT bp.*, u.username,
               COALESCE(bp.views, 0) as views,
               (SELECT COUNT(*) FROM blog_like WHERE blog_id = bp.id) as likes,
               (SELECT COUNT(*) FROM comment WHERE blog_id = bp.id) as comments
        FROM blog_post bp 
        JOIN user u ON bp.user_id = u.id 
        WHERE bp.title LIKE ? OR bp.content LIKE ?
        ORDER BY bp.created_at DESC
    ");
    $searchTerm = "%{$searchQuery}%";
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $stmt = $conn->prepare("
        SELECT bp.*, u.username,
               COALESCE(bp.views, 0) as views,
               (SELECT COUNT(*) FROM blog_like WHERE blog_id = bp.id) as likes,
               (SELECT COUNT(*) FROM comment WHERE blog_id = bp.id) as comments
        FROM blog_post bp 
        JOIN user u ON bp.user_id = u.id 
        ORDER BY bp.created_at DESC
    ");
    $stmt->execute();
}
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
            <div class="brand-section">
                <a href="index.php" class="logo"><?php echo APP_NAME; ?></a>
                <p class="brand-tagline">Where ideas come to life and stories find their voice</p>
            </div>
            
            <!-- Search Bar in Navigation -->
            <div class="nav-search">
                <form method="GET" action="index.php" class="nav-search-form">
                    <input type="text" name="search" placeholder="Search blogs..." value="<?php echo htmlspecialchars($searchQuery); ?>" class="nav-search-input">
                </form>
            </div>
            
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="create-blog.php" class="btn btn-primary">Create Blog</a>
                    
                    <!-- Profile Dropdown - Only show name and avatar -->
                    <div class="profile-dropdown">
                        <button class="profile-trigger" onclick="toggleProfileMenu()">
                            <div class="profile-avatar-small">
                                <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                            </div>
                            <span class="profile-name"><?php echo htmlspecialchars($currentUser['username']); ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </button>
                        
                        <!-- Dropdown appears on click -->
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
        <!-- Hero Section with Statistics -->
        <div class="hero-banner">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to <span class="highlight">Momentum</span></h1>
                <p class="hero-subtitle">Where ideas come to life and stories find their voice. Share your thoughts, connect with fellow writers, and engage through comments and likes.</p>
                <div class="hero-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="create-blog.php" class="btn btn-hero-primary">
                            <span>✍️</span>
                            <span>Write Your Story</span>
                        </a>
                        <a href="#blogs" class="btn btn-hero-secondary">
                            <span>📖</span>
                            <span>Explore Blogs</span>
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-hero-primary">
                            <span>🚀</span>
                            <span>Get Started</span>
                        </a>
                        <a href="#blogs" class="btn btn-hero-secondary">
                            <span>📖</span>
                            <span>Explore Blogs</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-number"><?php echo $totalBlogs; ?>+</div>
                    <div class="stat-label">Stories Published</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $totalUsers; ?>+</div>
                    <div class="stat-label">Active Writers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-number"><?php echo number_format($totalViews); ?>+</div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💬</div>
                    <div class="stat-number"><?php echo $totalComments; ?>+</div>
                    <div class="stat-label">Comments</div>
                </div>
            </div>
        </div>
        
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($searchQuery): ?>
            <div class="search-results-info">
                <p>Found <?php echo count($blogs); ?> result(s) for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"</p>
                <a href="index.php" class="btn btn-secondary">Clear Search</a>
            </div>
        <?php endif; ?>
        
        <!-- All Blogs Section -->
        <h2 class="section-title" id="blogs"><?php echo $searchQuery ? 'Search Results' : '📚 Latest Blog Posts'; ?></h2>
        
        <div class="blog-grid">
            <?php if (empty($blogs)): ?>
                <div class="empty-state">
                    <p><?php echo $searchQuery ? 'No blogs found matching your search.' : 'No blog posts yet. Be the first to create one!'; ?></p>
                    <?php if (isLoggedIn() && !$searchQuery): ?>
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
                            <span class="author">
                                By <a href="profile.php?id=<?php echo $blog['user_id']; ?>"><?php echo htmlspecialchars($blog['username']); ?></a>
                            </span>
                            <span class="date"><?php echo formatDate($blog['created_at']); ?></span>
                        </div>
                        <div class="blog-stats">
                            <span>❤️ <?php echo $blog['likes']; ?></span>
                            <span>💬 <?php echo $blog['comments']; ?></span>
                            <span>👁️ <?php echo $blog['views']; ?></span>
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
        // Profile dropdown toggle
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.profile-trigger') && !event.target.closest('.profile-trigger')) {
                const menu = document.getElementById('profileMenu');
                if (menu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                }
            }
        }
        
        // Submit search on Enter key
        document.querySelector('.nav-search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    </script>
</body>
</html>