<?php
require_once 'includes/db_connect.php';

// already logged send in home page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Online Voting System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>assets/images/logo.png" type="image/png">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-logo">
            <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Vote Logo">
        </div>

        <?php
        // Display any error or success messages
        if (isset($_SESSION['message'])) {
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'error';
            echo '<div class="message ' . $message_type . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form action="<?php echo BASE_URL; ?>php_actions/signup_action.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="form-group">
                <label for="cpassword">Confirm Password</label>
                <input type="password" id="cpassword" name="cpassword" class="form-control" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>

        <div class="auth-links">
            <a href="login.php">Already have an account? Sign In</a>
        </div>
    </div>

</body>
</html>
