<?php
require_once 'config/config.php';
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (createTables()) {
        $message = 'Database tables created successfully! You can now <a href="register.php">register</a> an account.';
    } else {
        $error = 'Failed to create database tables. Please check your database connection.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1>Install <?php echo APP_NAME; ?></h1>
            
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
            
            <p style="margin-bottom: 1.5rem;">Click the button below to create the required database tables.</p>
            
            <form method="POST">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Install Database</button>
            </form>
            
            <p class="auth-link">
                Already installed? <a href="index.php">Go to Home</a>
            </p>
        </div>
    </div>
</body>
</html>