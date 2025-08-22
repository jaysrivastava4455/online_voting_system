<?php

require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>assets/images/logo.png" type="image/png">
</head>
<body>

    <div class="header-container">
        <header class="main-header">
            <div class="container">
                <a href="<?php echo BASE_URL; ?>index.php" class="logo-link">
                    <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Voting System Logo" class="logo-img">
                    <span class="logo-text">ONLINE VOTING SYSTEM</span>
                </a>

                <nav class="main-nav">
                    <ul>
                        <!-- check user logged in -->
                        <?php if (isset($_SESSION['user_id'])) { ?>
                                <!-- Admin -->
                            <?php if ($_SESSION['user_role'] === 'admin') {  ?>
                                <li><a href="<?php echo BASE_URL; ?>admin/index.php">Home</a></li>
                                <li><a href="<?php echo BASE_URL; ?>admin/manage_elections.php">Elections</a></li>
                                <li><a href="<?php echo BASE_URL; ?>admin/manage_candidates.php">Candidates</a></li>
                                <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                            <!-- if voter -->
                            <?php } else {  ?>
                                <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                                <li><a href="<?php echo BASE_URL; ?>results.php">Results</a></li>
                                <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                            <?php } ?>

                        <?php } ?>
                    </ul>
                </nav>
            </div>
        </header>
        <?php if (isset($_SESSION['user_id'])) : ?>
            <div class="welcome-bar">
                <div class="container">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <main class="main-content">
        <div class="container">
