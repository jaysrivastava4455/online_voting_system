<?php
require_once '../includes/header.php';

// Admin Authentication Check 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

//  Get Election ID from URL 
if (!isset($_GET['election_id']) || !is_numeric($_GET['election_id'])) {
    $_SESSION['message'] = "No election selected.";
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
$election_id = $_GET['election_id'];

// Fetch Results Data 
try {
    //  Get the election topic.
    $stmt_election = $pdo->prepare("SELECT election_topic FROM elections WHERE id = ?");
    $stmt_election->execute([$election_id]);
    $election = $stmt_election->fetch();
    if (!$election) {
        throw new Exception("Election not found.");
    }
    $election_topic = $election['election_topic'];

    //  Get the results for each candidate.
    // This query joins candidates with votes, groups by candidate, and counts the votes.
    $sql = "
        SELECT 
            c.id, 
            c.candidate_name, 
            c.candidate_photo,
            COUNT(v.id) as vote_count
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id
        WHERE c.election_id = ?
        GROUP BY c.id, c.candidate_name, c.candidate_photo
        ORDER BY vote_count DESC
    ";
    $stmt_results = $pdo->prepare($sql);
    $stmt_results->execute([$election_id]);
    $results = $stmt_results->fetchAll();

    //  Calculate the total votes cast in this election for the percentage calculation.
    $total_votes = 0;
    foreach ($results as $result) {
        $total_votes += $result['vote_count'];
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Error fetching results: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}
?>

<style>
/* Styles for the results progress bar */
.progress-bar-container {
    width: 100%;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}
.progress-bar {
    height: 20px;
    background-color: #4CAF50;
    text-align: center;
    line-height: 20px;
    color: white;
    font-weight: bold;
    border-radius: 4px;
}
</style>

<h1 class="page-title">Results for: <?php echo htmlspecialchars($election_topic); ?></h1>
<p style="margin-bottom: 20px;">Total Votes Cast: <strong><?php echo $total_votes; ?></strong></p>
<!-- creating table to display result -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Candidate Name</th>
                <th>Votes Received</th>
                <th>Vote Share</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($results) > 0){ ?>
                <?php foreach ($results as $result){ ?>
                    <?php
                        // Calculate percentage. Avoid division by zero if no votes were cast.
                        $percentage = ($total_votes > 0) ? ($result['vote_count'] / $total_votes) * 100 : 0;
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo BASE_URL . 'assets/images/candidate_photos/' . htmlspecialchars($result['candidate_photo']); ?>" alt="<?php echo htmlspecialchars($result['candidate_name']); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                        </td>
                        <td><?php echo htmlspecialchars($result['candidate_name']); ?></td>
                        <td><strong><?php echo $result['vote_count']; ?></strong></td>
                        <td style="min-width: 200px;">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%;">
                                    <?php echo round($percentage, 1); ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr>
                    <td colspan="4" style="text-align: center;">No candidates found for this election.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/index.php" class="btn btn-primary" style="width: auto; padding: 10px 20px;">&laquo; Back to Dashboard</a>
</div>


<?php
require_once '../includes/footer.php';
?>
