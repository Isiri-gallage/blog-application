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
        SELECT bp.*, u.username, u.profile_picture,
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
        SELECT bp.*, u.username, u.profile_picture,
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

// Show hero banner logic:
// 1. Always show for non-logged-in users
// 2. For logged-in users, show only on first login
$showHeroBanner = false;

if (!isLoggedIn()) {
    // Not logged in - always show hero
    $showHeroBanner = true;
} else {
    // Logged in - check if first time
    if (!isset($_SESSION['has_seen_hero'])) {
        $showHeroBanner = true;
        $_SESSION['has_seen_hero'] = true;
    }
}
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
    <a href="index.php" class="logo">
        <img src="assets/images/logo.svg" alt="Momentum" style="height: 50px; display: block;">
    </a>
</div>
            
            <!-- Search Bar in Navigation -->
            <div class="nav-search">
                <form method="GET" action="index.php" class="nav-search-form">
                    <input type="text" name="search" placeholder="Search blogs..." value="<?php echo htmlspecialchars($searchQuery); ?>" class="nav-search-input">
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
                    
                    <!-- Profile Dropdown - Only show name and avatar -->
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
        <!-- Hero Section with Statistics - Only show on first visit -->
        <?php if ($showHeroBanner): ?>
        <div class="hero-banner">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to <span class="highlight">Momentum</span></h1>
                <p class="hero-subtitle">Where ideas come to life and stories find their voice. Share your thoughts, connect with fellow writers, and engage through comments and likes.</p>
                <div class="hero-actions">
                    <?php if (isLoggedIn()): ?>
                        <a href="create-blog.php" class="btn btn-hero-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Write Your Story</span>
                        </a>
                        <a href="#blogs" class="btn btn-hero-secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <span>Explore Blogs</span>
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-hero-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                <line x1="23" y1="11" x2="17" y2="11"></line>
                            </svg>
                            <span>Get Started</span>
                        </a>
                        <a href="#blogs" class="btn btn-hero-secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <span>Explore Blogs</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-number"><?php echo $totalBlogs; ?>+</div>
                    <div class="stat-label">Stories Published</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-number"><?php echo $totalUsers; ?>+</div>
                    <div class="stat-label">Active Writers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div class="stat-number"><?php echo number_format($totalViews); ?>+</div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-number"><?php echo $totalComments; ?>+</div>
                    <div class="stat-label">Comments</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- How to Use Section - Only show for non-logged-in users -->
        <?php if (!isLoggedIn()): ?>
        <div class="how-to-use-section">
            <div class="how-to-header">
                <h2 class="how-to-title">Start Publishing in Minutes</h2>
                <p class="how-to-subtitle">Simple steps to share your voice with the world</p>
            </div>
            
            <div class="steps-container">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Create Your Account</h3>
                    <p class="step-description">Sign up in seconds and customize your writer profile to reflect your unique voice.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Write Your Story</h3>
                    <p class="step-description">Use our powerful editor to craft compelling content with images, videos, and code.</p>
                </div>
                
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Reach Your Audience</h3>
                    <p class="step-description">Publish instantly and watch your story reach readers around the globe.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
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
        <h2 class="section-title" id="blogs"><?php echo $searchQuery ? 'Search Results' : 'Latest Blog Posts'; ?></h2>
        
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
                            <div class="author-info">
                                <?php if (isset($blog['profile_picture']) && $blog['profile_picture'] && file_exists($blog['profile_picture'])): ?>
                                    <img src="<?php echo $blog['profile_picture']; ?>" alt="<?php echo htmlspecialchars($blog['username']); ?>" class="author-avatar">
                                <?php else: ?>
                                    <div class="author-avatar avatar-fallback">
                                        <?php echo strtoupper(substr($blog['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="author">
                                    By <a href="profile.php?id=<?php echo $blog['user_id']; ?>"><?php echo htmlspecialchars($blog['username']); ?></a>
                                </span>
                            </div>
                            <span class="date"><?php echo formatDate($blog['created_at']); ?></span>
                        </div>
                        <div class="blog-stats">
                            <span class="likes">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                <?php echo $blog['likes']; ?>
                            </span>
                            <span class="comments">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <?php echo $blog['comments']; ?>
                            </span>
                            <span class="views">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <?php echo $blog['views']; ?>
                            </span>
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