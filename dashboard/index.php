<?php
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/Member.php';
require_once __DIR__ . '/../backend/Trainer.php';
/** @var PDO $conn Provided by backend/database.php through backend/auth.php. */
require_login();
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !verify_csrf_token()) {
    http_response_code(400);
    exit('Invalid request token. Refresh the page and try again.');
}

$requestedView = (string) ($_GET["view"] ?? "overview");
$activeView = in_array($requestedView, ["overview", "members", "trainers", "transactions"], true) ? $requestedView : "overview";
$transactionNotice = $_SESSION["transaction_notice"] ?? null;
unset($_SESSION["transaction_notice"]);
$member = new Member($conn);
$trainerManager = new Trainer($conn);
$trainers = $trainerManager->getTrainers();
$transactions = $activeView === 'transactions' ? Transaction::getHistory($conn) : [];

$allMembers = $member->getMembers();
$joinedThisMonth = count(array_filter($allMembers, static function (array $row): bool {
    return isset($row['date_registered']) && substr((string) $row['date_registered'], 0, 7) === date('Y-m');
}));
$latestMember = $allMembers[0] ?? null;
$recentMembers = array_slice($allMembers, 0, 5);

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $keyword = $_GET['search'];
    $members = $member->searchMembers($keyword);
} else {
    $members = $allMembers;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && isset($_POST["add_trainer"])) {
    $created = $trainerManager->addTrainer(
        trim((string) ($_POST["first_name"] ?? "")),
        trim((string) ($_POST["last_name"] ?? "")),
        trim((string) ($_POST["contact_number"] ?? "")),
        trim((string) ($_POST["specialty"] ?? "")),
        (string) current_user()['name']
    );
    $_SESSION["transaction_notice"] = [
        "type" => $created ? "success" : "danger",
        "message" => $created ? "Trainer added successfully." : "The trainer could not be added. Please check the details and try again.",
    ];
    header("Location: index.php?view=trainers");
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && isset($_POST["add_member"])) {

    $first_name = $_POST["first_name"] ?? "";
    $last_name = $_POST["last_name"] ?? "";
    $contact_number = $_POST["contact_number"] ?? "";
    $email = $_POST["email"] ?? "";
    $date_registered = $_POST["date_registered"] ?? "";
    $trainer_id = $_POST["trainer_id"] ?? "";

    $saved = $member->addMember(
        $first_name,
        $last_name,
        $contact_number,
        $email,
        $date_registered,
        $trainer_id,
        (string) current_user()['name']
    );
    $_SESSION["transaction_notice"] = [
        "type" => $saved ? "success" : "danger",
        "message" => $saved ? "Member registered successfully." : "The member could not be registered. Please check the details and try again.",
    ];

    header("Location: index.php?view=members");
    exit;
}
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && isset($_POST["update_member"])) {

    $member_id = $_POST["member_id"] ?? "";
    $first_name = $_POST["first_name"] ?? "";
    $last_name = $_POST["last_name"] ?? "";
    $contact_number = $_POST["contact_number"] ?? "";
    $email = $_POST["email"] ?? "";
    $date_registered = $_POST["date_registered"] ?? "";
    $trainer_id = $_POST["trainer_id"] ?? "";

    $saved = $member->updateMember(
        $member_id,
        $first_name,
        $last_name,
        $contact_number,
        $email,
        $date_registered,
        $trainer_id,
        (string) current_user()['name']
    );
    $_SESSION["transaction_notice"] = [
        "type" => $saved ? "success" : "danger",
        "message" => $saved ? "Member details updated successfully." : "The member details could not be updated. Please try again.",
    ];

    header("Location: index.php?view=members");
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST" && isset($_POST["delete_member"])) {

    $member_id = $_POST["member_id"] ?? "";

    $deleted = $member->deleteMember((string) $member_id, (string) current_user()['name']);
    $_SESSION["transaction_notice"] = [
        "type" => $deleted ? "success" : "danger",
        "message" => $deleted ? "Member deleted successfully." : "The member could not be deleted. Please try again.",
    ];

    header("Location: index.php?view=members");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Membership System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=8">
</head>

<body>

    <div class="gym-layout">
        <aside class="gym-sidebar" aria-label="Main navigation">
            <a class="gym-sidebar-brand" href="index.php?view=overview">
                <span class="gym-sidebar-brand-mark" aria-hidden="true">GM</span>
                <span>Gym Membership</span>
            </a>
            <p class="gym-sidebar-label">Menu</p>
            <nav class="gym-sidebar-nav">
                <a class="gym-sidebar-link <?= $activeView === "overview" ? "is-active" : "" ?>" href="index.php?view=overview"><span class="gym-sidebar-icon" aria-hidden="true">D</span>Dashboard</a>
                <a class="gym-sidebar-link <?= $activeView === "members" ? "is-active" : "" ?>" href="index.php?view=members"><span class="gym-sidebar-icon" aria-hidden="true">M</span>Members</a>
                <a class="gym-sidebar-link <?= $activeView === "trainers" ? "is-active" : "" ?>" href="index.php?view=trainers"><span class="gym-sidebar-icon" aria-hidden="true">TR</span>Trainers</a>
                <a class="gym-sidebar-link <?= $activeView === "transactions" ? "is-active" : "" ?>" href="index.php?view=transactions"><span class="gym-sidebar-icon" aria-hidden="true">TX</span>Transactions</a>
            </nav>
            <div class="gym-sidebar-account">
                <span class="gym-avatar" aria-hidden="true"><?= escape(strtoupper(substr(current_user()['name'], 0, 1))) ?></span>
                <div class="gym-account-copy">
                    <div class="gym-account-name"><?= escape(current_user()['name']) ?></div>
                    <div class="gym-account-status">Signed in</div>
                </div>
                <form method="post" action="../auth/logout.php" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <button class="gym-signout" type="submit">Sign out</button>
                </form>
            </div>
        </aside>

        <main class="gym-main" id="overview">
            <div class="gym-main-inner">

        <div class="page-header">
            <div>
                <p class="text-muted mb-1"><?= $activeView === "members" ? "Member management" : ($activeView === "trainers" ? "Trainer management" : ($activeView === "transactions" ? "Activity history" : "Dashboard")) ?></p>
                <h1><?= $activeView === "members" ? "Members" : ($activeView === "trainers" ? "Trainers" : ($activeView === "transactions" ? "Transactions" : "Gym Membership")) ?></h1>
                <p class="text-muted">
                    <?= $activeView === "members" ? "View, search, add, and manage gym member records." : ($activeView === "trainers" ? "Manage your gym trainers and their specialties." : ($activeView === "transactions" ? "Review member and trainer activity." : "Manage and monitor your registered gym members.")) ?>
                </p>
            </div>

            <?php if ($activeView === "trainers"): ?>
            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addTrainerModal">+ Add Trainer</button>
            <?php elseif ($activeView !== "transactions"): ?>
            <button type="button"
                    class="btn btn-primary add-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#addMemberModal">
                + Add Member
            </button>
            <?php endif; ?>
        </div>

        <?php if (is_array($transactionNotice) && isset($transactionNotice["message"], $transactionNotice["type"])): ?>
            <div class="alert alert-<?= escape((string) $transactionNotice["type"]) ?> alert-dismissible fade show" role="status">
                <?= escape((string) $transactionNotice["message"]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($activeView === "overview"): ?>
<section class="welcome-guide" aria-labelledby="welcome-title">
            <div>
                <p class="welcome-eyebrow mb-1">Welcome to your dashboard</p>
                <h2 id="welcome-title">Hello, <?= escape(current_user()['name']) ?>!</h2>
                <p class="mb-0">Here’s a quick guide to managing your gym members:</p>
            </div>
            <ol class="welcome-steps mb-0">
                <li>Choose <strong>+ Add Member</strong> to register a new gym member.</li>
                <li>Use the search box to find a member by name, phone number, or email.</li>
                <li>Use <strong>Edit</strong> or <strong>Delete</strong> in a member’s row to manage their record.</li>
            </ol>
        </section>

        <section class="dashboard-overview" aria-label="Gym membership overview">
            <div class="dashboard-stat-grid">
                <article class="dashboard-stat-card dashboard-stat-primary">
                    <div class="dashboard-stat-top"><span class="dashboard-stat-icon">MEM</span><span class="dashboard-stat-tag">ALL TIME</span></div>
                    <p class="dashboard-stat-label">Total members</p>
                    <p class="dashboard-stat-value"><?= number_format(count($allMembers)) ?></p>
                    <p class="dashboard-stat-note">Registered in your gym</p>
                </article>
                <article class="dashboard-stat-card dashboard-stat-light">
                    <div class="dashboard-stat-top"><span class="dashboard-stat-icon">NEW</span><span class="dashboard-stat-tag">THIS MONTH</span></div>
                    <p class="dashboard-stat-label">New members</p>
                    <p class="dashboard-stat-value"><?= number_format($joinedThisMonth) ?></p>
                    <p class="dashboard-stat-note">Joined <?= escape(date('F Y')) ?></p>
                </article>
                <article class="dashboard-stat-card dashboard-stat-light dashboard-stat-latest">
                    <div class="dashboard-stat-top"><span class="dashboard-stat-icon">TOP</span><span class="dashboard-stat-tag">LATEST SIGN-UP</span></div>
                    <p class="dashboard-stat-label">Newest member</p>
                    <p class="dashboard-stat-value dashboard-stat-name"><?= $latestMember ? escape(trim((string) $latestMember['first_name'] . ' ' . (string) $latestMember['last_name'])) : 'No members yet' ?></p>
                    <p class="dashboard-stat-note"><?= $latestMember ? 'Registered ' . escape((string) $latestMember['date_registered']) : 'Add your first member to get started' ?></p>
                </article>
            </div>

            <div class="dashboard-lower-grid">
                <section class="dashboard-panel dashboard-recent-panel" aria-labelledby="recent-members-title">
                    <div class="dashboard-panel-heading">
                        <div><p class="dashboard-panel-eyebrow">YOUR GYM</p><h2 id="recent-members-title">Recent members</h2></div>
                        <a href="index.php?view=members" class="dashboard-text-link">View all <span aria-hidden="true">&rarr;</span></a>
                    </div>
                    <?php if ($recentMembers === []): ?>
                        <div class="dashboard-empty-state"><span class="dashboard-empty-mark">+</span><p>No members to show yet.</p><button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addMemberModal">Add your first member</button></div>
                    <?php else: ?>
                        <div class="dashboard-recent-list">
                            <?php foreach ($recentMembers as $recent): ?>
                                <div class="dashboard-recent-row">
                                    <span class="dashboard-member-avatar"><?= escape(strtoupper(substr((string) $recent['first_name'], 0, 1))) ?></span>
                                    <div class="dashboard-recent-person"><strong><?= escape(trim((string) $recent['first_name'] . ' ' . (string) $recent['last_name'])) ?></strong><span><?= escape((string) $recent['email']) ?></span></div>
                                    <span class="dashboard-member-date"><?= escape((string) $recent['date_registered']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <aside class="dashboard-action-panel" aria-label="Quick actions">
                    <p class="dashboard-panel-eyebrow">READY WHEN YOU ARE</p>
                    <h2>Keep your gym moving.</h2>
                    <p>Add a member or manage the records you already have.</p>
                    <button type="button" class="dashboard-action-button" data-bs-toggle="modal" data-bs-target="#addMemberModal"><span aria-hidden="true">+</span> Register a member</button>
                    <a href="index.php?view=members" class="dashboard-action-link">Open member management <span aria-hidden="true">&rarr;</span></a>
                </aside>
            </div>
        </section>
<?php endif; ?>
<?php if ($activeView === "members"): ?>
    <section id="members" aria-label="Gym member records">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div><p class="text-muted small text-uppercase fw-bold mb-1">Member records</p><h2 class="h4 mb-0">Gym members</h2></div>
    </div>
    <form method="GET" class="mb-3">
    <input type="hidden" name="view" value="members">
    <div class="input-group">
        <input type="text"
               name="search"
               class="form-control"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

        <button type="submit" class="btn btn-primary">
            Search
        </button>

        <a href="index.php?view=members" class="btn btn-secondary">
            Clear
        </a>
    </div>
</form>

    <?php if (empty($members)): ?>

        <p>No members found.</p>

    <?php else: ?>

        <table border="1" cellpadding="10" class="table">
            <tr>
                <th>ID</th>
                <th>Trainer ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Date Registered</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($members as $row): ?>

                <tr>
                    <td><?= htmlspecialchars($row['member_id']) ?></td>
                    <td><?= htmlspecialchars($row['trainer_id']) ?></td>
                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['date_registered']) ?></td>

                    <td>
                        <button type="button"
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editMemberModal<?= $row['member_id'] ?>">
                            Edit
                        </button>

    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteMemberModal<?= (int) $row['member_id'] ?>">Delete</button>
                    </td>
                </tr>

            <?php endforeach; ?>

        </table>

<?php require __DIR__ . '/../modals/edit_member.php'; ?>
<?php require __DIR__ . '/../modals/delete_member.php'; ?>

    <?php endif; ?>
    </section>
<?php endif; ?>
<?php if ($activeView === "trainers"): ?>
    <section class="transaction-history trainer-page" aria-labelledby="trainer-list-title">
        <div class="transaction-history-heading">
            <div><p class="dashboard-panel-eyebrow">GYM TEAM</p><h2 id="trainer-list-title">Your trainers</h2></div>
            <span class="transaction-count"><?= number_format(count($trainers)) ?> trainers</span>
        </div>
        <?php if ($trainers === []): ?>
            <div class="transaction-empty"><span class="transaction-empty-mark">T</span><h3>No trainers added yet</h3><p>Add trainers here, then select them when registering gym members.</p><button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addTrainerModal">Add your first trainer</button></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table transaction-table align-middle mb-0">
                    <thead><tr><th>ID</th><th>Trainer</th><th>Contact number</th><th>Specialty</th></tr></thead>
                    <tbody>
                    <?php foreach ($trainers as $trainer): ?>
                        <tr>
                            <td><span class="trainer-id-tag">#<?= (int) $trainer['trainer_id'] ?></span></td>
                            <td><div class="trainer-name-cell"><span class="dashboard-member-avatar"><?= escape(strtoupper(substr((string) $trainer['first_name'], 0, 1))) ?></span><strong><?= escape(trim((string) $trainer['first_name'] . ' ' . (string) $trainer['last_name'])) ?></strong></div></td>
                            <td><?= escape((string) $trainer['contact_number']) ?></td>
                            <td><span class="trainer-specialty-tag"><?= escape((string) $trainer['specialty']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php if ($activeView === "transactions"): ?>
    <section class="transaction-history" aria-labelledby="transaction-history-title">
        <div class="transaction-history-heading">
            <div><p class="dashboard-panel-eyebrow">RECENT ACTIVITY</p><h2 id="transaction-history-title">System activity</h2></div>
            <span class="transaction-count"><?= number_format(count($transactions)) ?> records</span>
        </div>
        <?php if ($transactions === []): ?>
            <div class="transaction-empty"><span class="transaction-empty-mark">T</span><h3>No transactions yet</h3><p>Successful member and trainer actions will appear here.</p><a href="index.php?view=members" class="btn btn-primary add-btn">Go to Members</a></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table transaction-table align-middle mb-0">
                    <thead><tr><th>Action</th><th>Details</th><th>Performed by</th><th>Date and time</th></tr></thead>
                    <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><span class="transaction-badge transaction-<?= escape(strtolower((string) $transaction['action_type'])) ?>"><?= escape((string) $transaction['action_type']) ?></span></td>
                            <td class="fw-semibold"><?= escape((string) $transaction['description']) ?></td>
                            <td><?= escape((string) $transaction['performed_by']) ?></td>
                            <td class="transaction-date"><?= escape((string) $transaction['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
</div>

<?php require __DIR__ . '/../modals/add_member.php'; ?>
<?php require __DIR__ . '/../modals/add_trainer.php'; ?>
        </main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

