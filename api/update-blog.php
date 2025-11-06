<?php
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
$blogId = intval($_POST['id'] ?? 0);
$title = sanitize($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

// Validation
if (empty($blogId) || empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$conn = getDBConnection();

// Check if blog exists and user owns it
$stmt = $conn->prepare("SELECT user_id, featured_image FROM blog_post WHERE id = ?");
$stmt->execute([$blogId]);
$blog = $stmt->fetch();

if (!$blog) {
    echo json_encode(['success' => false, 'message' => 'Blog not found']);
    exit();
}

if ($blog['user_id'] != getCurrentUserId()) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this blog']);
    exit();
}

// Handle featured image upload
$featuredImage = $blog['featured_image']; // Keep existing image by default

if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['featured_image'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed']);
        exit();
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB']);
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = '../uploads/blog-images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('blog_') . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Delete old image if it exists
        if ($blog['featured_image'] && file_exists('../' . $blog['featured_image'])) {
            unlink('../' . $blog['featured_image']);
        }
        $featuredImage = 'uploads/blog-images/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit();
    }
}

// Check if user wants to remove the image
if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
    // Delete old image if it exists
    if ($blog['featured_image'] && file_exists('../' . $blog['featured_image'])) {
        unlink('../' . $blog['featured_image']);
    }
    $featuredImage = null;
}

// Update blog post
try {
    $stmt = $conn->prepare("UPDATE blog_post SET title = ?, content = ?, featured_image = ? WHERE id = ?");
    $stmt->execute([$title, $content, $featuredImage, $blogId]);
    
    echo json_encode(['success' => true, 'message' => 'Blog updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update blog']);
}
?>