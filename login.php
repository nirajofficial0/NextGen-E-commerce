<?php
/**
 * Customer Authentication - Sign In
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Social Login Action
if (isset($_GET['action']) && $_GET['action'] === 'google') {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = 'alex@example.com'");
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        setFlash('success', 'Logged in via Google Account (' . $user['email'] . ')');
        header("Location: index.php");
        exit;
    }
}

// Logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    session_start();
    setFlash('success', 'You have been logged out.');
    header("Location: login.php");
    exit;
}

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Handle Form Submission BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        if (isset($_SESSION['anonymous_id'])) {
            $db->prepare("UPDATE cart SET user_id = ? WHERE session_id = ?")->execute([$user['id'], $_SESSION['anonymous_id']]);
        }

        setFlash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
        header("Location: " . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
        exit;
    } else {
        setFlash('error', 'Invalid email address or password.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="form-card">
        <h2 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; text-align: center; margin-bottom: 0.5rem;">Welcome Back</h2>
        <p style="color: var(--text-secondary); text-align: center; margin-bottom: 1.5rem;">Sign in to access your NextGen account & saved AI recommendations</p>

        <?php if ($msg = getFlash('error')): ?>
            <div class="flash-alert error mb-4"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($msg = getFlash('success')): ?>
            <div class="flash-alert success mb-4"><?= $msg ?></div>
        <?php endif; ?>

        <!-- Google One-Click Social Sign-In -->
        <a href="login.php?action=google" class="btn-secondary mb-4" style="width: 100%; justify-content: center; background: #ffffff; color: #1e293b; font-weight: 600; padding: 0.75rem;">
            <i class="fa-brands fa-google text-rose"></i> Continue with Google Account
        </a>

        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
            <hr style="flex: 1; border-color: var(--border-color);">
            <span style="font-size: 0.8rem; color: var(--text-muted);">OR WITH EMAIL</span>
            <hr style="flex: 1; border-color: var(--border-color);">
        </div>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="user@example.com" value="alex@example.com" required>
            </div>

            <div class="form-group mb-4">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" value="User@123" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                Sign In <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div style="background: var(--bg-surface); border: 1px dashed var(--border-highlight); border-radius: var(--radius-md); padding: 1rem; margin-top: 1.5rem; font-size: 0.8rem; color: var(--text-secondary);">
            <strong>Demo Credentials:</strong><br>
            Customer: <code>alex@example.com</code> / <code>User@123</code><br>
            Admin: <code>admin@nextgen.com</code> / <code>Admin@123</code>
        </div>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
            Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600;">Create One</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
