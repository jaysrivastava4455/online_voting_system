<?php
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //get data 
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

   
    if (empty($username) || empty($email) || empty($password) || empty($cpassword)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "signup.php");
        exit();
    }
// validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "signup.php");
        exit();
    }
// validate password
    if (strlen($password) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters long.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "signup.php");
        exit();
    }
//check password and confirm password are same?
    if ($password !== $cpassword) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "signup.php");
        exit();
    }

    //  Check for Existing User 
    try {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "An account with this email already exists.";
            $_SESSION['message_type'] = "error";
            header("Location: " . BASE_URL . "signup.php");
            exit();
        }

        //Insert New User
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'voter')";
        $stmt = $pdo->prepare($sql);

      
        if ($stmt->execute([$username, $email, $hashed_password])) {  
            $_SESSION['message'] = "Registration successful! Please log in.";
            $_SESSION['message_type'] = "success";
            header("Location: " . BASE_URL . "login.php");
            exit();
        } else {
            $_SESSION['message'] = "Could not register the user. Please try again.";
            $_SESSION['message_type'] = "error";
            header("Location: " . BASE_URL . "signup.php");
            exit();
        }

    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

} else {
    header("Location: " . BASE_URL . "signup.php");
    exit();
}
?>
