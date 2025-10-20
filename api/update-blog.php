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
$stmt = $conn->prepare("SELECT user_id FROM blog_post WHERE id = ?");
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

// Update blog post
try {
    $stmt = $conn->prepare("UPDATE blog_post SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $content, $blogId]);
    
    echo json_encode(['success' => true, 'message' => 'Blog updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update blog']);
}
?>