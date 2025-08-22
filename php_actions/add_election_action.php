<?php

require_once '../includes/db_connect.php';


// Ensure the user is a logged-in admin.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //  Get and Sanitize Form Data 
    $election_topic = trim($_POST['election_topic']);
    $num_candidates = trim($_POST['num_candidates']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $admin_id = $_SESSION['user_id']; 

    //  Server-Side Validation 
    if (empty($election_topic) || empty($num_candidates) || empty($start_date) || empty($end_date)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_elections.php");
        exit();
    }

    if (!is_numeric($num_candidates) || $num_candidates < 2) {
        $_SESSION['message'] = "Number of candidates must be at least 2.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_elections.php");
        exit();
    }
    
    $today = date("Y-m-d");
    if ($start_date < $today) {
        $_SESSION['message'] = "Start date cannot be in the past.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_elections.php");
        exit();
    }

    if ($end_date < $start_date) {
        $_SESSION['message'] = "Ending date cannot be before the starting date.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_elections.php");
        exit();
    }

    //  Insert into Database
    try {
        // The `status` is 'pending' by default. It will become 'active' on the start date.
        $sql = "INSERT INTO elections (election_topic, num_candidates, start_date, end_date, status, created_by) 
                VALUES (?, ?, ?, ?, 'pending', ?)";
        
        $stmt = $pdo->prepare($sql);
        
      
        if ($stmt->execute([$election_topic, $num_candidates, $start_date, $end_date, $admin_id])) {
            
            $_SESSION['message'] = "Election has been created successfully!";
            $_SESSION['message_type'] = "success";
        } else {
         
            $_SESSION['message'] = "Failed to create the election. Please try again.";
            $_SESSION['message_type'] = "error";
        }

    } catch (PDOException $e) {
       
        $_SESSION['message'] = "Database error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }

    // Redirect back to the manage elections page to show the message and updated list.
    header("Location: " . BASE_URL . "admin/manage_elections.php");
    exit();

} else {
    // If accessed directly, redirect away.
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
?>
