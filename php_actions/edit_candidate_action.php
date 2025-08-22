<?php
require_once '../includes/db_connect.php';


// loggid in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve all the data sent from the edit_candidate.php form.
    $candidate_id = $_POST['candidate_id'];
    $candidate_name = trim($_POST['candidate_name']);
    $candidate_details = trim($_POST['candidate_details']);

    //  Server-Side Validation 
    // Check for empty required fields.
    if (empty($candidate_id) || empty($candidate_name) || empty($candidate_details)) {
        $_SESSION['message'] = "Name and details are required.";
        $_SESSION['message_type'] = "error";
        // Redirect back to the specific edit form if validation fails.
        header("Location: " . BASE_URL . "admin/edit_candidate.php?id=" . $candidate_id);
        exit();
    }

    try {
        //  Handle Photo Upload (if a new photo is provided)
        $new_photo_name = null;
        if (isset($_FILES['candidate_photo']) && $_FILES['candidate_photo']['error'] == 0) {
            
            // First, get the filename of the current photo so we can delete it.
            $stmt_old_photo = $pdo->prepare("SELECT candidate_photo FROM candidates WHERE id = ?");
            $stmt_old_photo->execute([$candidate_id]);
            $old_photo_name = $stmt_old_photo->fetchColumn();

            // Process the newly uploaded photo
            $photo = $_FILES['candidate_photo'];
            $photo_ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (in_array($photo_ext, $allowed)) {
                // Create a unique filename to prevent overwriting other files.
                $new_photo_name = "candidate_" . uniqid('', true) . "." . $photo_ext;
                $destination = '../assets/images/candidate_photos/' . $new_photo_name;
                
                if (move_uploaded_file($photo['tmp_name'], $destination)) {
                    // If the new photo is moved successfully, delete the old one from the server.
                    if ($old_photo_name && file_exists('../assets/images/candidate_photos/' . $old_photo_name)) {
                        unlink('../assets/images/candidate_photos/' . $old_photo_name);
                    }
                } else {
                    throw new Exception("Failed to move uploaded file.");
                }
            } else {
                throw new Exception("Invalid file type. Only JPG, JPEG, PNG are allowed.");
            }
        }

        //  Update the Database
        if ($new_photo_name) {
            // If a new photo was uploaded, update all fields including the photo filename.
            $sql = "UPDATE candidates SET candidate_name = ?, candidate_details = ?, candidate_photo = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$candidate_name, $candidate_details, $new_photo_name, $candidate_id]);
        } else {
            // If no new photo was uploaded, only update the text fields.
            $sql = "UPDATE candidates SET candidate_name = ?, candidate_details = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$candidate_name, $candidate_details, $candidate_id]);
        }

        $_SESSION['message'] = "Candidate updated successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: " . BASE_URL . "admin/manage_candidates.php");
        exit();

    } catch (Exception $e) {
        // Catch any errors during the process and redirect back with a message.
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/edit_candidate.php?id=" . $candidate_id);
        exit();
    }
} else {
    
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
?>
