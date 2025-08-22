<?php
require_once 'includes/db_connect.php';

// check logged already 
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Voting System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>assets/images/logo.png" type="image/png">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-logo">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Vote Logo" class="auth-logo-login"  >
        </div>

        <h2 style="color: #fff; margin-bottom: 20px;">Login</h2>

        <?php
        //display message send from page (signup etc)
        if (isset($_SESSION['message'])) {
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'error';
            echo '<div class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form action="<?php echo BASE_URL; ?>php_actions/login_action.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="auth-links">
            <a href="signup.php">Don't have an account? Sign Up</a>
        </div>
    </div>

</body>
</html>
