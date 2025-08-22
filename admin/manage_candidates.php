<?php

require_once '../includes/header.php';

// admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}


try {
    // Fetch elections that are not yet expired to populate the dropdown.
    $sql_elections = "SELECT id, election_topic FROM elections WHERE status != 'expired' ORDER BY election_topic ASC";
    $stmt_elections = $pdo->query($sql_elections);
    $available_elections = $stmt_elections->fetchAll();

    //  Fetch all existing candidates along with their election topic.
    $sql_candidates = "
        SELECT 
            c.id, c.candidate_name, c.candidate_details, c.candidate_photo,
            e.election_topic 
        FROM candidates c
        JOIN elections e ON c.election_id = e.id
        ORDER BY c.created_at DESC
    ";
    $stmt_candidates = $pdo->query($sql_candidates);
    $candidates = $stmt_candidates->fetchAll();

} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>

<h1 class="page-title">Manage Candidates</h1>

<div style="display: flex; gap: 30px;">

    <!-- Left Side: Add New Candidate Form -->
    <div style="flex: 1;">
        <div class="admin-form-section">
            <h3>Add New Candidate</h3>

            <?php
            // Display any session messages.
            if (isset($_SESSION['message'])) {
                $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'error';
                echo '<div class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            
            
            <form action="<?php echo BASE_URL; ?>php_actions/add_candidate_action.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="election_id">Select Election</label>
                    <select name="election_id" id="election_id" class="form-control" required>
                        <option value="">-- Choose an Election --</option>
                        <?php foreach ($available_elections as $election): ?>
                            <option value="<?php echo $election['id']; ?>">
                                <?php echo htmlspecialchars($election['election_topic']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="candidate_name">Candidate Name</label>
                    <input type="text" name="candidate_name" id="candidate_name" class="form-control" placeholder="Candidate Name" required>
                </div>
                <div class="form-group">
                    <label for="candidate_photo">Candidate Photo</label>
                    <input type="file" name="candidate_photo" id="candidate_photo" class="form-control" placeholder="Candidate Photo" required>
                </div>
                <div class="form-group">
                    <label for="candidate_details">Candidate Details</label>
                    <textarea name="candidate_details" id="candidate_details" class="form-control" rows="4" placeholder="Candidate Details" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Candidate</button>
            </form>
        </div>
    </div>

    <!-- Right Side: Candidate Details Table -->
    <div style="flex: 2;">
        <div class="table-container">
            <h3>Candidate Details</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Details</th>
                        <th>Election</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($candidates) > 0): ?>
                        <?php foreach ($candidates as $index => $candidate): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <img src="<?php echo BASE_URL . 'assets/images/candidate_photos/' . htmlspecialchars($candidate['candidate_photo']); ?>" alt="<?php echo htmlspecialchars($candidate['candidate_name']); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($candidate['candidate_name']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['candidate_details']); ?></td>
                                <td><?php echo htmlspecialchars($candidate['election_topic']); ?></td>
                                <td class="action-buttons">
                                    <a  href="edit_candidate.php?id=<?php echo $candidate['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="<?php echo BASE_URL; ?>php_actions/delete_candidate_action.php?id=<?php echo $candidate['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No candidates have been added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

require_once '../includes/footer.php';
?>
