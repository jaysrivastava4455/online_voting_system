<?php
require_once '../includes/header.php';
date_default_timezone_set('Asia/Kolkata');

//  Admin Authentication Check 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}


// auto update election status
try {
    $current_date = date("Y-m-d");

    //  Update 'pending' elections to 'active' 
    $sql_activate = "UPDATE elections SET status = 'active' WHERE start_date <= ? AND status = 'pending'";
    $stmt_activate = $pdo->prepare($sql_activate);
    $stmt_activate->execute([$current_date]);

    //  Update 'active' elections to 'expired' 
    $sql_expire = "UPDATE elections SET status = 'expired' WHERE end_date < ? AND status = 'active'";
    $stmt_expire = $pdo->prepare($sql_expire);
    $stmt_expire->execute([$current_date]);

} catch (PDOException $e) {
    die("Error updating election statuses: " . $e->getMessage());
}


//  Fetch all existing elections to display in the table 
try {
    $sql = "SELECT * FROM elections ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $elections = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching elections: " . $e->getMessage());
}
?>

<h1 class="page-title">Manage Elections</h1>

<div style="display: flex; gap: 30px; flex-wrap: wrap;">

    <!-- Left Side: Add New Election Form -->
    <div style="flex: 1; min-width: 300px;">
        <div class="admin-form-section">
            <h3>Add New Election</h3>
            
            <?php
            // Display any success or error messages from the form submission.
            if (isset($_SESSION['message'])) {
                $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'error';
                echo '<div class="message ' . $message_type . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <form action="<?php echo BASE_URL; ?>php_actions/add_election_action.php" method="POST">
                <div class="form-group">
                    <label for="election_topic">Election Topic</label>
                    <input type="text" name="election_topic" id="election_topic" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="num_candidates">Number of Candidates</label>
                    <input type="number" name="num_candidates" id="num_candidates" class="form-control" required min="2">
                </div>
                <div class="form-group">
                    <label for="start_date">Starting Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Ending Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Election</button>
            </form>
        </div>
    </div>

    <!-- Right Side: Upcoming Elections Table -->
    <div style="flex: 2; min-width: 600px;">
        <div class="table-container">
            <h3>Upcoming & Existing Elections</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Election Name</th>
                        <th>Candidates</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($elections) > 0): ?>
                        <?php foreach ($elections as $index => $election): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($election['election_topic']); ?></td>
                                <td><?php echo htmlspecialchars($election['num_candidates']); ?></td>
                                <td><?php echo date("d-m-Y", strtotime($election['start_date'])); ?></td>
                                <td><?php echo date("d-m-Y", strtotime($election['end_date'])); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($election['status'])); ?></td>
                                <td class="action-buttons">
                                    <div class="action-button-group">
                                        <a href="edit_election.php?id=<?php echo $election['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="<?php echo BASE_URL; ?>php_actions/delete_election_action.php?id=<?php echo $election['id']; ?>" class="btn-delete" onclick="return confirm('WARNING: Are you sure you want to delete this election? This will permanently delete all of its candidates and votes. This action cannot be undone.');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No elections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Adjust path for the footer.
require_once '../includes/footer.php';
?>
