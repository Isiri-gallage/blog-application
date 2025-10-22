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

$commentId = intval($_POST['id'] ?? 0);

if (empty($commentId)) {
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit();
}

$conn = getDBConnection();

// Check if comment exists and user owns it
$stmt = $conn->prepare("SELECT user_id FROM comment WHERE id = ?");
$stmt->execute([$commentId]);
$comment = $stmt->fetch();

if (!$comment) {
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit();
}

if ($comment['user_id'] != getCurrentUserId()) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment']);
    exit();
}

// Delete comment
try {
    $stmt = $conn->prepare("DELETE FROM comment WHERE id = ?");
    $stmt->execute([$commentId]);
    
    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
}
?>