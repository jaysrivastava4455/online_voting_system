<?php
require_once './includes/db_connect.php';

//AUTHENTICATE THE USER
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// admin
if ($_SESSION['user_role'] !== 'voter') {
    header("Location: " . BASE_URL . "admin/index.php");
    exit();
}

require_once './includes/header.php';

try {
    // Get all expired elections
    $sql = "
        SELECT 
            e.id,
            e.election_topic,
            e.end_date
        FROM elections e
        WHERE e.status = 'expired'
        ORDER BY e.end_date DESC
    ";
    $stmt = $pdo->query($sql);
    $expired_elections = $stmt->fetchAll();

    $results = [];

    foreach ($expired_elections as $election) {
        $election_id = $election['id'];

        // Get vote counts per candidate
        $stmt = $pdo->prepare("
            SELECT c.candidate_name, COUNT(v.id) as vote_count
            FROM votes v
            JOIN candidates c ON v.candidate_id = c.id
            WHERE v.election_id = ?
            GROUP BY v.candidate_id
            ORDER BY vote_count DESC
        ");
        $stmt->execute([$election_id]);
        $candidates = $stmt->fetchAll();

        if (count($candidates) === 0) {
            $winner = null; // No votes cast
        } elseif (
            count($candidates) > 1 &&
            $candidates[0]['vote_count'] === $candidates[1]['vote_count']
        ) {
            $winner = "Draw"; //tie
        } else {
            $winner = $candidates[0]['candidate_name']; // Clear winner
        }

        $results[] = [
            'election_topic' => $election['election_topic'],
            'end_date' => $election['end_date'],
            'winner_name' => $winner
        ];
    }

} catch (PDOException $e) {
    die("Database Error: Could not fetch election results. " . $e->getMessage());
}
?>

<h1 class="page-title">Election Results</h1>
<p style="margin-bottom: 20px;">Here are the winners for all completed elections.</p>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Election Name</th>
                <th>Ended On</th>
                <th>Winning Candidate</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($results) > 0){ ?>
                <?php foreach ($results as $index => $election){ ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($election['election_topic']); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($election['end_date'])); ?></td>
                        <td>
                            <?php if ($election['winner_name'] === null): ?>
                                <span style="color: #777;">No votes cast</span>
                            <?php elseif ($election['winner_name'] === "Draw"): ?>
                                <span style="color: #777;">Draw</span>
                            <?php else: ?>
                                <strong><?php echo htmlspecialchars($election['winner_name']); ?></strong>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else{ ?>
                <tr>
                    <td colspan="4" style="text-align: center;">There are no completed elections to show results for yet.</td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</div>

<?php

require_once './includes/footer.php';
?>
