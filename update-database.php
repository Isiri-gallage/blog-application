<?php
require_once 'config/config.php';
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        
        // Create comments table
        $commentTable = "CREATE TABLE IF NOT EXISTS comment (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            blog_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (blog_id) REFERENCES blog_post(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        )";
        
        // Create likes table
        $likeTable = "CREATE TABLE IF NOT EXISTS blog_like (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            blog_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (blog_id, user_id),
            FOREIGN KEY (blog_id) REFERENCES blog_post(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        )";
        
        // Add bio column to user table (if not exists)
        $addBioColumn = "ALTER TABLE user ADD COLUMN IF NOT EXISTS bio TEXT";
        
        $conn->exec($commentTable);
        $conn->exec($likeTable);
        
        try {
            $conn->exec($addBioColumn);
        } catch (PDOException $e) {
            // Column might already exist, ignore error
        }
        
        $message = 'Database updated successfully! New features are ready to use.';
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
            <h1>Update Database</h1>
            
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
            
            <p style="margin-bottom: 1.5rem;">Click the button below to add new tables for comments and likes.</p>
            
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