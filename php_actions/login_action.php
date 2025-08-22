<?php
require_once '../includes/db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

  //chech empty email or password
    if (empty($email) || empty($password)) {
        $_SESSION['message'] = "Email and password are required.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    try {
// select user from database
        $sql = "SELECT id, username, email, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        // Check if a user was found.
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();

            // --- Verify the Password --
            if (password_verify($password, $user['password'])) {

                // --- Set Session Variables ---
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // admin
                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "admin/index.php");
                } else {
                    header("Location: " . BASE_URL . "index.php");
                }
                exit();

            } else {
                $_SESSION['message'] = "Invalid email or password.";
                $_SESSION['message_type'] = "error";
                header("Location: " . BASE_URL . "login.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Invalid email or password.";
            $_SESSION['message_type'] = "error";
            header("Location: " . BASE_URL . "login.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

} else {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
?>
