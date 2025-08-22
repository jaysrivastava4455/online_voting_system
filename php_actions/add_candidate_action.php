<?php
require_once '../includes/db_connect.php';

//  Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = "Unauthorized access.";
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //  Get Form Data 
    $election_id = trim($_POST['election_id']);
    $candidate_name = trim($_POST['candidate_name']);
    $candidate_details = trim($_POST['candidate_details']);
    $admin_id = $_SESSION['user_id'];

    //  Basic Validation 
    if (empty($election_id) || empty($candidate_name) || empty($candidate_details)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_candidates.php");
        exit();
    }

    // Check Candidate Limit 
    try {
        // First, get the max number of candidates allowed for this election.
        $stmt_limit = $pdo->prepare("SELECT num_candidates FROM elections WHERE id = ?");
        $stmt_limit->execute([$election_id]);
        $election = $stmt_limit->fetch();
        $max_candidates = $election['num_candidates'];

        // Then, count how many candidates are already added to this election.
        $stmt_count = $pdo->prepare("SELECT COUNT(*) as count FROM candidates WHERE election_id = ?");
        $stmt_count->execute([$election_id]);
        $current_candidates = $stmt_count->fetch()['count'];

        if ($current_candidates >= $max_candidates) {
            $_SESSION['message'] = "The maximum number of candidates for this election has been reached.";
            $_SESSION['message_type'] = "error";
            header("Location: " . BASE_URL . "admin/manage_candidates.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error checking candidate limit: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_candidates.php");
        exit();
    }


    // File Upload Handling 
    if (isset($_FILES['candidate_photo']) && $_FILES['candidate_photo']['error'] == 0) {
        $photo = $_FILES['candidate_photo'];
        $photo_name = $photo['name'];
        $photo_tmp_name = $photo['tmp_name'];
        $photo_size = $photo['size'];
        $photo_error = $photo['error'];

        // Get the file extension.
        $photo_ext = explode('.', $photo_name);
        $photo_actual_ext = strtolower(end($photo_ext));

        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (in_array($photo_actual_ext, $allowed_extensions)) {
            if ($photo_error === 0) {
                if ($photo_size < 5000000) { // 5MB limit
                    // Create a unique name for the file to avoid overwriting.
                    $photo_new_name = "candidate_" . uniqid('', true) . "." . $photo_actual_ext;
                    $photo_destination = '../assets/images/candidate_photos/' . $photo_new_name;
                    
                    // Move the file from the temporary location to the final destination.
                    if (move_uploaded_file($photo_tmp_name, $photo_destination)) {
                        // --- File uploaded successfully, now insert into DB ---
                        try {
                            $sql = "INSERT INTO candidates (election_id, candidate_name, candidate_details, candidate_photo, created_by) 
                                    VALUES (?, ?, ?, ?, ?)";
                            $stmt = $pdo->prepare($sql);
                            
                            if ($stmt->execute([$election_id, $candidate_name, $candidate_details, $photo_new_name, $admin_id])) {
                                $_SESSION['message'] = "Candidate added successfully!";
                                $_SESSION['message_type'] = "success";
                            } else {
                                $_SESSION['message'] = "Failed to add candidate to the database.";
                                $_SESSION['message_type'] = "error";
                            }
                        } catch (PDOException $e) {
                            $_SESSION['message'] = "Database error: " . $e->getMessage();
                            $_SESSION['message_type'] = "error";
                        }
                    } else {
                        $_SESSION['message'] = "Failed to move the uploaded file.";
                        $_SESSION['message_type'] = "error";
                    }
                } else {
                    $_SESSION['message'] = "Your file is too large (Max 5MB).";
                    $_SESSION['message_type'] = "error";
                }
            } else {
                $_SESSION['message'] = "There was an error uploading your file.";
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "You cannot upload files of this type (Allowed: jpg, jpeg, png).";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Photo is required.";
        $_SESSION['message_type'] = "error";
    }

    // Redirect back to the manage candidates page.
    header("Location: " . BASE_URL . "admin/manage_candidates.php");
    exit();

} else {
    // If accessed directly, redirect away.
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
?>
