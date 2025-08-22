<?php
require_once '../includes/db_connect.php';

// Security & Authentication 
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
//  Must be a voter
if ($_SESSION['user_role'] === 'admin') {
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
//  Must be a POST request from the form
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

//  Get Data 
$voter_id = $_SESSION['user_id'];
$election_id = $_POST['election_id'] ?? null;
$candidate_id = $_POST['candidate_id'] ?? null;

// Validation 
if (empty($election_id) || empty($candidate_id)) {
    $_SESSION['message'] = "You must select a candidate to vote.";
    $_SESSION['message_type'] = "error";
    // Redirect back to the specific voting page 
    header("Location: " . BASE_URL . "vote.php?election_id=" . $election_id);
    exit();
}

// Process the Vote using a Transaction 
try {
    // Start a transaction
    $pdo->beginTransaction();

    //  Re-verify that the election is currently active.
    $stmt_election = $pdo->prepare("SELECT status FROM elections WHERE id = ?");
    $stmt_election->execute([$election_id]);
    $election_status = $stmt_election->fetchColumn();

    if ($election_status !== 'active') {
        throw new Exception("This election is no longer active.");
    }

    //  Re-verify that the voter has not already voted (critical check).
    $stmt_voted = $pdo->prepare("SELECT id FROM votes WHERE election_id = ? AND voter_id = ?");
    $stmt_voted->execute([$election_id, $voter_id]);
    if ($stmt_voted->rowCount() > 0) {
        throw new Exception("You have already cast your vote in this election.");
    }

    //  Insert the vote into the database.
    $sql = "INSERT INTO votes (election_id, voter_id, candidate_id) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$election_id, $voter_id, $candidate_id]);

    // If all steps were successful, commit the transaction.
    $pdo->commit();

    $_SESSION['message'] = "Your vote has been cast successfully! Thank you for participating.";
    $_SESSION['message_type'] = "success";
    header("Location: " . BASE_URL . "index.php");
    exit();

} catch (Exception $e) {
    // If any error occurred, roll back the transaction.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Set an error message and redirect.
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "vote.php?election_id=" . $election_id);
    exit();
}
?>
