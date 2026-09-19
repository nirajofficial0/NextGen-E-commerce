<?php
/**
 * Admin Portal Login
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdmin()) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid administrator credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal Login | NextGen E-Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: radial-gradient(circle at center, #131b2e, #090d16);">

    <div class="form-card" style="width: 100%; max-width: 440px; margin: 0;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div class="logo-icon" style="margin: 0 auto 1rem auto; width: 54px; height: 54px; font-size: 1.6rem;"><i class="fa-solid fa-shield-halved"></i></div>
            <h2 style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800;">ADMIN CONTROL CENTER</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Sign in to access store metrics & AI Customer Analytics</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="flash-alert error mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" class="form-control" value="admin@nextgen.com" required>
            </div>

            <div class="form-group mb-4">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="Admin@123" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                <i class="fa-solid fa-lock"></i> Access Admin Console
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="../index.php" style="font-size: 0.85rem; color: var(--text-muted);">&larr; Return to Customer Store</a>
        </div>
    </div>

</body>
</html>
