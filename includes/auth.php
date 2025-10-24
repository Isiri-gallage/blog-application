<?php
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    require_once __DIR__ . '/../config/database.php';
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username, email, role, profile_picture FROM user WHERE id = ?");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

// Require login (redirect if not logged in)
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit();
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit();
    }
}
?>