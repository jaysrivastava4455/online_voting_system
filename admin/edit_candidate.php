<?php
require_once '../includes/header.php';

// --- Admin Authentication & ID Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "admin/manage_candidates.php");
    exit();
}
$candidate_id = $_GET['id'];

// --- Fetch existing candidate data ---
try {
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch();
    if (!$candidate) {
        $_SESSION['message'] = "Candidate not found.";
        $_SESSION['message_type'] = "error";
        header("Location: " . BASE_URL . "admin/manage_candidates.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h1 class="page-title">Edit Candidate</h1>

<div class="admin-form-section" style="max-width: 600px; margin: 0 auto;">
    <h3>Editing: <?php echo htmlspecialchars($candidate['candidate_name']); ?></h3>

    <form action="<?php echo BASE_URL; ?>php_actions/edit_candidate_action.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
        
        <div class="form-group">
            <label for="candidate_name">Candidate Name</label>
            <input type="text" name="candidate_name" id="candidate_name" class="form-control" value="<?php echo htmlspecialchars($candidate['candidate_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="candidate_details">Candidate Details</label>
            <textarea name="candidate_details" id="candidate_details" class="form-control" rows="4" required><?php echo htmlspecialchars($candidate['candidate_details']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Current Photo</label>
            <div>
                <img src="<?php echo BASE_URL . 'assets/images/candidate_photos/' . htmlspecialchars($candidate['candidate_photo']); ?>" alt="Current Photo" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
            </div>
        </div>

        <div class="form-group">
            <label for="candidate_photo">Upload New Photo (Optional)</label>
            <input type="file" name="candidate_photo" id="candidate_photo" class="form-control">
            <small>Only choose a file if you want to replace the current photo.</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Candidate</button>
        <a href="manage_candidates.php" class="btn" style="background-color: #6c757d; color: white; width: auto; margin-left: 10px;">Cancel</a>
    </form>
</div>

<?php
require_once '../includes/footer.php';
?>
