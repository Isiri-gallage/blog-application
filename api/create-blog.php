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
$title = sanitize($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');

// Validation
if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit();
}

// Insert blog post
$conn = getDBConnection();

try {
    $stmt = $conn->prepare("INSERT INTO blog_post (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([getCurrentUserId(), $title, $content]);
    
    $blogId = $conn->lastInsertId();
    
    echo json_encode(['success' => true, 'message' => 'Blog created successfully', 'blog_id' => $blogId]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to create blog']);
}
?>