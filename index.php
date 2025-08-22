<?php
require_once './includes/db_connect.php';

//  AUTHENTICATION AND REDIRECTION LOGIC
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit(); 
}

// admin
if ($_SESSION['user_role'] === 'admin') {
    header("Location: " . BASE_URL . "admin/index.php");
    exit(); 
}


//voter. LOAD THE PAGE HEADER
require_once './includes/header.php';

$voter_id = $_SESSION['user_id'];
// fetch election and check voter has voted 0/1

try {
   
    $sql = "
        SELECT 
            e.id, 
            e.election_topic, 
            e.num_candidates, 
            e.start_date, 
            e.end_date,
            (SELECT COUNT(*) FROM votes v WHERE v.voter_id = ? AND v.election_id = e.id) as has_voted
        FROM elections e
        WHERE e.status = 'active'
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$voter_id]);
    $elections = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: Could not fetch elections. " . $e->getMessage());
}

?>

<h1 class="page-title">Voter's Panel - Active Elections</h1>

<div class="table-container">
    <?php // display any success and error message
    if (isset($_SESSION['message'])){ ?>
        <div class="message <?php echo htmlspecialchars($_SESSION['message_type']); ?>">
            <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php } ?>

    <?php if (count($elections) > 0) { ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Election Name</th>
                    <th>Candidates</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($elections as $index => $election) { ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($election['election_topic']); ?></td>
                        <td><?php echo htmlspecialchars($election['num_candidates']); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($election['start_date'])); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($election['end_date'])); ?></td>
                        <td class="action-buttons">
                            <?php if ($election['has_voted']) { ?>
                               <!-- disable the vote button  -->
                                <button class="btn-view" disabled style="background-color: #9E9E9E; cursor: not-allowed;">Voted</button>
                            <?php }else {?>
                                <!-- Otherwise, show button -->
                                <a href="vote.php?election_id=<?php echo $election['id']; ?>" class="btn-view">Vote Now</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php }else { ?>
      <!-- no active election are there -->
        <p style="text-align: center; padding: 20px;">There are no active elections at the moment.</p>
    <?php } ?>
</div>

<?php

require_once './includes/footer.php';
?>
