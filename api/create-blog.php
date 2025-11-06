<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and sanitize input
$title = sanitize($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

// Validation
if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit();
}

// Handle featured image upload
$featuredImage = null;
if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['featured_image'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed']);
        exit();
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB']);
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/blogs/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            exit();
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
        exit();
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('blog_') . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $featuredImage = 'uploads/blogs/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image. Check folder permissions.']);
        exit();
    }
}

// Insert blog post
$conn = getDBConnection();

try {
    $stmt = $conn->prepare("INSERT INTO blog_post (user_id, title, content, featured_image, views) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([getCurrentUserId(), $title, $content, $featuredImage]);
    
    $blogId = $conn->lastInsertId();
    
    echo json_encode(['success' => true, 'message' => 'Blog created successfully', 'blog_id' => $blogId]);
} catch (PDOException $e) {
    // If database insert fails, delete uploaded image
    if ($featuredImage && file_exists($uploadDir . basename($featuredImage))) {
        unlink($uploadDir . basename($featuredImage));
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>