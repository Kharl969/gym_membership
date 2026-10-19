<?php
require_once __DIR__ . '/../backend/auth.php';
/** @var PDO $conn Provided by backend/database.php. */

if (current_user() !== null) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$firstName = '';
$lastName = '';
$gender = '';
$email = '';
$username = '';
$allowedGenders = ['Male', 'Female', 'Other', 'Prefer not to say'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName = trim((string) ($_POST['last_name'] ?? ''));
    $gender = (string) ($_POST['gender'] ?? '');
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $fullName = trim($firstName . ' ' . $lastName);

    if (!verify_csrf_token()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($firstName === '' || strlen($firstName) > 60 || $lastName === '' || strlen($lastName) > 60) {
        $error = 'Enter your first and last name (up to 60 characters each).';
    } elseif (!in_array($gender, $allowedGenders, true)) {
        $error = 'Choose a gender option.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $error = 'Enter a valid email address.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $username)) {
        $error = 'Username must be 3-40 characters and use only letters, numbers, dots, dashes, or underscores.';
    } elseif (strlen($password) < 8) {
        $error = 'Choose a password with at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        try {
            $check = $conn->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
            $check->execute(['username' => $username, 'email' => $email]);
            if ($check->fetchColumn()) {
                $error = 'That username or email is already registered.';
            } else {
                $insert = $conn->prepare(
                    'INSERT INTO users (name, first_name, last_name, full_name, username, email, password_hash, contact_number, gender, address)
                     VALUES (:name, :first_name, :last_name, :full_name, :username, :email, :password_hash, :contact_number, :gender, :address)'
                );
                $insert->execute([
                    'name' => $fullName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'full_name' => $fullName,
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'contact_number' => '',
                    'gender' => $gender,
                    'address' => '',
                ]);

                Session::signIn([
                    'id' => (int) $conn->lastInsertId(),
                    'name' => $fullName,
                    'username' => $username,
                ]);
                header('Location: ../index.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Could not create the account. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account | Gym Membership</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=9">
</head>
<body class="auth-page">
<div class="auth-layout auth-layout-register">
    <aside class="auth-showcase">
        <a class="auth-showcase-brand" href="../index.php"><span class="auth-showcase-mark">GM</span> GYM MEMBERSHIP</a>
        <div class="auth-showcase-copy">
            <p class="auth-showcase-eyebrow">GET STARTED TODAY</p>
            <h2>Build a stronger<br>gym community.</h2>
            <p>Create your account to organize members, trainers, and gym activity.</p>
        </div>
        <div class="auth-showcase-points"><span>01 <b>Members</b></span><span>02 <b>Trainers</b></span><span>03 <b>Activity</b></span></div>
        <span class="auth-decoration auth-decoration-one" aria-hidden="true"></span>
        <span class="auth-decoration auth-decoration-two" aria-hidden="true"></span>
    </aside>
    <main class="auth-card auth-register-card">
        <div class="auth-mobile-brand"><span class="auth-showcase-mark">GM</span> GYM MEMBERSHIP</div>
        <div class="auth-form-heading"><p class="auth-form-eyebrow">JOIN THE TEAM</p><h1>Create your account</h1><p class="text-muted">Fill in your details to get started.</p></div>
        <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= escape($error) ?></div><?php endif; ?>
    <form method="post" class="auth-register-form">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <div class="mb-3">
            <label class="form-label" for="first_name">First name</label>
            <input class="form-control" type="text" id="first_name" name="first_name" value="<?= escape($firstName) ?>" maxlength="60" autocomplete="given-name" placeholder="e.g. Juan" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="last_name">Last name</label>
            <input class="form-control" type="text" id="last_name" name="last_name" value="<?= escape($lastName) ?>" maxlength="60" autocomplete="family-name" placeholder="e.g. Dela Cruz" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="gender">Gender</label>
            <select class="form-select" id="gender" name="gender" required>
                <option value="" disabled <?= $gender === '' ? 'selected' : '' ?>>Select gender</option>
                <?php foreach ($allowedGenders as $option): ?>
                    <option value="<?= escape($option) ?>" <?= $gender === $option ? 'selected' : '' ?>><?= escape($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= escape($email) ?>" maxlength="254" autocomplete="email" placeholder="e.g. juan.delacruz@email.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input class="form-control" type="text" id="username" name="username" value="<?= escape($username) ?>" minlength="3" maxlength="40" autocomplete="username" placeholder="e.g. juan.delacruz" required>
            <div class="form-text">Use 3-40 letters, numbers, dots, dashes, or underscores.</div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
                <input class="form-control" type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters" required>
                <button class="btn btn-outline-secondary password-toggle" type="button" data-password-target="password" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="confirm_password">Confirm password</label>
            <div class="input-group">
                <input class="form-control" type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" placeholder="Re-enter your password" required>
                <button class="btn btn-outline-secondary password-toggle" type="button" data-password-target="confirm_password" aria-label="Show confirm password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>
        <button class="btn btn-primary w-100" type="submit">Create account</button>
    </form>
        <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
    </main>
</div>
<script>
document.querySelectorAll('[data-password-target]').forEach(function (button) {
    button.addEventListener('click', function () {
        const input = document.getElementById(button.dataset.passwordTarget);
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.setAttribute('aria-pressed', reveal ? 'true' : 'false');
        button.setAttribute('aria-label', (reveal ? 'Hide ' : 'Show ') + (input.id === 'confirm_password' ? 'confirm password' : 'password'));
        button.querySelector('i').className = reveal ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});
</script>
</body>
</html>
