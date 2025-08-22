<?php
require_once '../includes/db_connect.php';

//  Security & Authentication 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get Election ID from URL and Validate 
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid election ID.";
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "admin/manage_elections.php");
    exit();
}
$election_id = $_GET['id'];

try {
    //  Get all candidate photos for this election BEFORE deleting
    $stmt_photos = $pdo->prepare("SELECT candidate_photo FROM candidates WHERE election_id = ?");
    $stmt_photos->execute([$election_id]);
    $photos_to_delete = $stmt_photos->fetchAll(PDO::FETCH_COLUMN);

    //  Delete the election from the database
    // Because of the 'ON DELETE CASCADE' constraint in your database setup,
    // deleting the election will automatically delete all associated candidates and votes.
    $stmt_delete = $pdo->prepare("DELETE FROM elections WHERE id = ?");
    $stmt_delete->execute([$election_id]);

    // Check if the deletion was successful
    if ($stmt_delete->rowCount() > 0) {
        //  Delete the physical photo files from the server
        if (!empty($photos_to_delete)) {
            foreach ($photos_to_delete as $photo_filename) {
                $photo_path = '../assets/images/candidate_photos/' . $photo_filename;
                if (file_exists($photo_path)) {
                    unlink($photo_path); // Deletes the file
                }
            }
        }
        $_SESSION['message'] = "Election and all associated data have been deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to delete election. It may have already been removed.";
        $_SESSION['message_type'] = "error";
    }

} catch (PDOException $e) {
    $_SESSION['message'] = "Database error during deletion: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

// Redirect back to the manage elections page.
header("Location: " . BASE_URL . "admin/manage_elections.php");
exit();
?>
