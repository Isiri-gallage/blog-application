<?php
require_once 'config/config.php';
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        
        // Add profile_picture column to user table
        $addProfilePicColumn = "ALTER TABLE user ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL";
        
        try {
            $conn->exec($addProfilePicColumn);
            
            // Create uploads directory if it doesn't exist
            $uploadsDir = __DIR__ . '/uploads/profiles';
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            
            $message = 'Database updated successfully! Profile picture feature is now enabled.';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                $message = 'Profile picture column already exists. No update needed.';
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
    <title>Add Profile Picture - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Add Profile Picture Feature</h1>
            
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
            
            <p style="margin-bottom: 1.5rem;">Click the button below to add profile picture support.</p>
            
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