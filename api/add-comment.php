<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$blogId = intval($_POST['blog_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if (empty($blogId) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Blog ID and comment content are required']);
    exit();
}

$conn = getDBConnection();

// Check if blog exists
$stmt = $conn->prepare("SELECT id FROM blog_post WHERE id = ?");
$stmt->execute([$blogId]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Blog not found']);
    exit();
}

// Insert comment
try {
    $stmt = $conn->prepare("INSERT INTO comment (blog_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$blogId, getCurrentUserId(), $content]);
    
    // Get the comment with user info
    $commentId = $conn->lastInsertId();
    $stmt = $conn->prepare("
        SELECT c.*, u.username 
        FROM comment c 
        JOIN user u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Comment added successfully',
        'comment' => $comment
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
}
?>