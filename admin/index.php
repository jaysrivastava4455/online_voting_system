<?php

require_once '../includes/header.php';
date_default_timezone_set('Asia/Kolkata');

// Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Auto-update Election Status
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


// Fetch All Elections for Display 
try {
    $sql = "SELECT * FROM elections ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $elections = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching elections: " . $e->getMessage());
}

?>

<h1 class="page-title">Admin Dashboard - Elections</h1>
<!-- display the election detail -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Election Name</th>
                <th>Candidates</th>
                <th>Starting Date</th>
                <th>Ending Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($elections) > 0) { ?>
                <?php foreach ($elections as $index => $election) { ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($election['election_topic']); ?></td>
                        <td><?php echo htmlspecialchars($election['num_candidates']); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($election['start_date'])); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($election['end_date'])); ?></td>
                        <td>
                            <span class="status-<?php echo htmlspecialchars(strtolower($election['status'])); ?>">
                                <?php echo htmlspecialchars(ucfirst($election['status'])); ?>
                            </span>
                        </td>
                        <td class="action-buttons">

                            <a href="results.php?election_id=<?php echo $election['id']; ?>" class="btn-view">View Results</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No elections have been created yet.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php

require_once '../includes/footer.php';
?>
