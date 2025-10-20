<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

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

// Get blog ID
$blogId = intval($_POST['id'] ?? 0);

if (empty($blogId)) {
    echo json_encode(['success' => false, 'message' => 'Blog ID is required']);
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
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this blog']);
    exit();
}

// Delete blog post
try {
    $stmt = $conn->prepare("DELETE FROM blog_post WHERE id = ?");
    $stmt->execute([$blogId]);
    
    echo json_encode(['success' => true, 'message' => 'Blog deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete blog']);
}
?>