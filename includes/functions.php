<?php
// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Truncate text
function truncateText($text, $length = 150) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

// Simple Markdown to HTML converter
function markdownToHtml($markdown) {
    // Headers
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $markdown);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    
    // Bold
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    
    // Italic
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    
    // Links
    $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html);
    
    // Line breaks
    $html = nl2br($html);
    
    return $html;
}

// Set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>