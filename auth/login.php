<?php
require_once __DIR__ . '/../backend/auth.php';
/** @var PDO $conn Provided by backend/database.php. */

if (current_user() !== null) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!verify_csrf_token()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, full_name, username, password_hash FROM users WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute(['username' => $username, 'email' => $username]);
        $account = $stmt->fetch();
        if ($account && password_verify($password, $account['password_hash'])) {
            Session::signIn(['id' => (int) $account['id'], 'name' => $account['full_name'] ?: $account['name'], 'username' => $account['username'] ?? '']);
            header('Location: ../index.php');
            exit;
        }
        $error = 'Username or password is incorrect.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Gym Membership</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=9">
</head>
<body class="auth-page">
<div class="auth-layout">
    <aside class="auth-showcase">
        <a class="auth-showcase-brand" href="../index.php"><span class="auth-showcase-mark">GM</span> GYM MEMBERSHIP</a>
        <div class="auth-showcase-copy">
            <p class="auth-showcase-eyebrow">YOUR GYM. YOUR COMMUNITY.</p>
            <h2>Stronger starts<br>with showing up.</h2>
            <p>Keep your member records and trainer team organized in one place.</p>
        </div>
        <div class="auth-showcase-footer"><span class="auth-showcase-dot"></span> Membership management made simple</div>
        <span class="auth-decoration auth-decoration-one" aria-hidden="true"></span>
        <span class="auth-decoration auth-decoration-two" aria-hidden="true"></span>
    </aside>
    <main class="auth-card">
        <div class="auth-mobile-brand"><span class="auth-showcase-mark">GM</span> GYM MEMBERSHIP</div>
        <div class="auth-form-heading"><p class="auth-form-eyebrow">WELCOME BACK</p><h1>Sign in</h1><p class="text-muted">Sign in to manage your gym.</p></div>
        <?php if ($error !== ''): ?><div class="alert alert-danger" role="alert"><?= escape($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <div class="mb-3"><label class="form-label" for="username">Username or email</label><input class="form-control" type="text" id="username" name="username" value="<?= escape($username) ?>" autocomplete="username" placeholder="Enter your username or email" required></div>
            <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" autocomplete="current-password" placeholder="Enter your password" required></div>
            <button class="btn btn-primary w-100" type="submit">Sign in to your dashboard</button>
        </form>
        <p class="auth-switch">New to the gym? <a href="register.php">Create an account</a></p>
    </main>
</div>
</body>
</html>
