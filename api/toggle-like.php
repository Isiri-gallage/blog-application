<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

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

if (empty($blogId)) {
    echo json_encode(['success' => false, 'message' => 'Blog ID is required']);
    exit();
}

$conn = getDBConnection();
$userId = getCurrentUserId();

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM blog_like WHERE blog_id = ? AND user_id = ?");
$stmt->execute([$blogId, $userId]);
$existingLike = $stmt->fetch();

try {
    if ($existingLike) {
        // Unlike
        $stmt = $conn->prepare("DELETE FROM blog_like WHERE blog_id = ? AND user_id = ?");
        $stmt->execute([$blogId, $userId]);
        $action = 'unliked';
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO blog_like (blog_id, user_id) VALUES (?, ?)");
        $stmt->execute([$blogId, $userId]);
        $action = 'liked';
    }
    
    // Get total likes
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM blog_like WHERE blog_id = ?");
    $stmt->execute([$blogId]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes' => $result['total']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to toggle like']);
}
?>