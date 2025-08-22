<?php
require_once '../includes/db_connect.php';

// Security & Authentication 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get Candidate ID from URL 
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid candidate ID.";
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "admin/manage_candidates.php");
    exit();
}
$candidate_id = $_GET['id'];

try {
    // Get the photo filename before deleting the record 
    $stmt_photo = $pdo->prepare("SELECT candidate_photo FROM candidates WHERE id = ?");
    $stmt_photo->execute([$candidate_id]);
    $candidate = $stmt_photo->fetch();

    if ($candidate) {
        $photo_filename = $candidate['candidate_photo'];

        // Delete the candidate record from the database
        $stmt_delete = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
        $stmt_delete->execute([$candidate_id]);

        if ($stmt_delete->rowCount() > 0) {
            //  Delete the photo file from the server 
            $photo_path = '../assets/images/candidate_photos/' . $photo_filename;
            if (file_exists($photo_path)) {
                unlink($photo_path); // Deletes the file
            }
            $_SESSION['message'] = "Candidate deleted successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to delete candidate.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Candidate not found.";
        $_SESSION['message_type'] = "error";
    }

} catch (PDOException $e) {
    // If the candidate has votes, the database constraint will prevent deletion.
    if ($e->getCode() == '23000') { // Integrity constraint violation
        $_SESSION['message'] = "Cannot delete this candidate because they have already received votes.";
    } else {
        $_SESSION['message'] = "Database error: " . $e->getMessage();
    }
    $_SESSION['message_type'] = "error";
}

// Redirect back to the manage candidates page.
header("Location: " . BASE_URL . "admin/manage_candidates.php");
exit();
?>
