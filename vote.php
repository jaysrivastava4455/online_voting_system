<?php
require_once './includes/db_connect.php';

// not voter and not set in session
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get and validate the election ID from the URL
if (!isset($_GET['election_id']) || !is_numeric($_GET['election_id'])) {
    $_SESSION['message'] = "Invalid election selected.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}
$election_id = $_GET['election_id'];
$voter_id = $_SESSION['user_id'];

require_once './includes/header.php';

try {
    // Check if the election is valid and active
    $stmt_election = $pdo->prepare("SELECT * FROM elections WHERE id = ? AND status = 'active'");
    $stmt_election->execute([$election_id]);
    $election = $stmt_election->fetch();

    if (!$election) {
        $_SESSION['message'] = "This election is not active or does not exist.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }

    // Check if the user has already voted
    $stmt_voted = $pdo->prepare("SELECT id FROM votes WHERE election_id = ? AND voter_id = ?");
    $stmt_voted->execute([$election_id, $voter_id]);
    if ($stmt_voted->rowCount() > 0) {
        $_SESSION['message'] = "You have already voted in this election.";
        $_SESSION['message_type'] = "error";
        header("Location: index.php");
        exit();
    }

    // Fetch all candidates for this election
    $stmt_candidates = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ?");
    $stmt_candidates->execute([$election_id]);
    $candidates = $stmt_candidates->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>
    .vote-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .candidate-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    .candidate-card {
        background-color: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.25s ease-in-out;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .candidate-card:hover {
        border-color: #4CAF50;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }
    
    .candidate-card.selected {
        border-color: #4CAF50;
        background-color: #f7fff7;
        box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
        transform: translateY(-5px) scale(1.02);
    }
    .candidate-card.selected::after {
        content: '✔';
        font-size: 16px;
        font-weight: bold;
        color: white;
        background-color: #4CAF50;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 10px;
        right: 10px;
        animation: pop-in 0.3s ease;
    }
    @keyframes pop-in {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .candidate-card img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 4px solid #f0f0f0;
    }
    .candidate-card h4 {
        margin-bottom: 10px;
        font-size: 1.3rem;
        color: #212121;
        font-weight: 700;
    }
    .candidate-card p {
        font-size: 0.95rem;
        color: #666;
    }
    .candidate-card input[type="radio"] {
        display: none;
    }
    .vote-button-container {
        text-align: center;
        margin-top: 40px;
    }
    .vote-button-container .btn {
        width: auto;
        padding: 15px 50px;
        font-size: 1.2rem;
        border-radius: 30px;
    }
</style>

<div class="vote-container">
    <h1 class="page-title">Cast Your Vote: <?php echo htmlspecialchars($election['election_topic']); ?></h1>
    <p style="text-align: center; color: #555; font-size: 1.1rem;">Select one candidate below and click the "Cast Your Vote" button to submit.</p>

    <form action="<?php echo BASE_URL; ?>php_actions/cast_vote_action.php" method="POST" id="voteForm">
        <!-- hidden input field  -->
        <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">

        <div class="candidate-cards">
            <?php if (empty($candidates)) : ?>
                <p>No candidates are available for this election.</p>
            <?php else : ?>
                <?php foreach ($candidates as $candidate) : ?>
                    <label class="candidate-card" for="candidate-<?php echo $candidate['id']; ?>">
                        <input type="radio" name="candidate_id" value="<?php echo $candidate['id']; ?>" id="candidate-<?php echo $candidate['id']; ?>" required>
                        <img src="<?php echo BASE_URL . 'assets/images/candidate_photos/' . htmlspecialchars($candidate['candidate_photo']); ?>" alt="Photo of <?php echo htmlspecialchars($candidate['candidate_name']); ?>">
                        <h4><?php echo htmlspecialchars($candidate['candidate_name']); ?></h4>
                        <p><?php echo htmlspecialchars($candidate['candidate_details']); ?></p>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($candidates)) : ?>
            <div class="vote-button-container">
                <button type="submit" class="btn btn-primary">Cast Your Vote</button>
            </div>
        <?php endif; ?>
    </form>
</div>
<?php
require_once './includes/footer.php';
?>