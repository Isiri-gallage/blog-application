<?php
require_once 'config/config.php';
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        
        // Add views column to blog_post table
        $addViewsColumn = "ALTER TABLE blog_post ADD COLUMN views INT(11) DEFAULT 0";
        
        try {
            $conn->exec($addViewsColumn);
            $message = 'Database updated successfully! Views tracking is now enabled.';
        } catch (PDOException $e) {
            // Column might already exist
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                $message = 'Views column already exists. No update needed.';
            } else {
                throw $e;
            }
        }
        
    } catch (PDOException $e) {
        $error = 'Failed to update database: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Database - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Update Database for Views</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <p style="margin-bottom: 1.5rem;">Click the button below to add views tracking to blog posts.</p>
            
            <form method="POST">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Database</button>
            </form>
            
            <p class="auth-link">
                <a href="index.php">Go to Home</a>
            </p>
        </div>
    </div>
</body>
</html>