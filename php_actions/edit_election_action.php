<?php
require_once '../includes/db_connect.php';

// Set the timezone to ensure correct date comparison.
date_default_timezone_set('Asia/Kolkata');

//  Security & Authentication 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get Form Data 
    $election_id = $_POST['election_id'];
    $election_topic = trim($_POST['election_topic']);
    $num_candidates = trim($_POST['num_candidates']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    //  Validation
    if (empty($election_id) || empty($election_topic) || empty($num_candidates) || empty($start_date) || empty($end_date)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/edit_election.php?id=" . $election_id);
        exit();
    }
    if ($end_date < $start_date) {
        $_SESSION['message'] = "Ending date cannot be before the starting date.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/edit_election.php?id=" . $election_id);
        exit();
    }


    // Determine the correct status based on the new dates.
    $current_date = date("Y-m-d");
    $new_status = '';

    if ($start_date > $current_date) {
        $new_status = 'pending';
    } elseif ($current_date >= $start_date && $current_date <= $end_date) {
        $new_status = 'active';
    } else {
        $new_status = 'expired';
    }


   
    try {
        // The SQL query is updated to include the new status.
        $sql = "UPDATE elections SET 
                    election_topic = ?, 
                    num_candidates = ?, 
                    start_date = ?, 
                    end_date = ?,
                    status = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$election_topic, $num_candidates, $start_date, $end_date, $new_status, $election_id])) {
            $_SESSION['message'] = "Election updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: " . BASE_URL . "admin/manage_elections.php");
            exit();
        } else {
            $_SESSION['message'] = "Failed to update election.";
            $_SESSION['message_type'] = "error";
            header("Location: " . BASE_URL . "admin/edit_election.php?id=" . $election_id);
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/edit_election.php?id=" . $election_id);
        exit();
    }

} else {
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
?>
