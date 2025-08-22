<?php

require_once '../includes/header.php';

// Admin Authentication & ID Check 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
// Ensure an ID was passed in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "admin/manage_elections.php");
    exit();
}
$election_id = $_GET['id'];

// Fetch the existing election data from the database 
try {
    $stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$election_id]);
    $election = $stmt->fetch();
    
    // If no election is found with that ID, redirect back
    if (!$election) {
        $_SESSION['message'] = "Election not found.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_elections.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h1 class="page-title">Edit Election</h1>

<div class="admin-form-section" style="max-width: 600px; margin: 0 auto;">
    <h3>Editing: <?php echo htmlspecialchars($election['election_topic']); ?></h3>
    
    <form action="<?php echo BASE_URL; ?>php_actions/edit_election_action.php" method="POST">
        <!-- Hidden input to pass the election ID to the action script -->
        <input type="hidden" name="election_id" value="<?php echo $election['id']; ?>">

        <div class="form-group">
            <label for="election_topic">Election Topic</label>
            <input type="text" name="election_topic" id="election_topic" class="form-control" value="<?php echo htmlspecialchars($election['election_topic']); ?>" required>
        </div>
        <div class="form-group">
            <label for="num_candidates">Number of Candidates</label>
            <input type="number" name="num_candidates" id="num_candidates" class="form-control" value="<?php echo htmlspecialchars($election['num_candidates']); ?>" required min="2">
        </div>
        <div class="form-group">
            <label for="start_date">Starting Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($election['start_date']); ?>" required>
        </div>
        <div class="form-group">
            <label for="end_date">Ending Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($election['end_date']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Election</button>
        <a href="manage_elections.php" class="btn" style="background-color: #6c757d; color: white; width: auto; margin-left: 10px;">Cancel</a>
    </form>
</div>

<?php
require_once '../includes/footer.php';
?>
