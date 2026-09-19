<?php
/**
 * Customer Authentication - Registration
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Handle Form Submission BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPass = $_POST['confirm_password'];
    $phone = sanitizeInput($_POST['phone']);

    if ($password !== $confirmPass) {
        setFlash('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters long.');
    } else {
        $db = Database::getConnection();
        
        // Check duplicate email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            setFlash('error', 'An account with this email address already exists.');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->execute([$fullName, $email, $hashed, $phone]);

            $newUserId = $db->lastInsertId();

            // Auto-login new registered user
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'customer';

            // Associate guest cart
            if (isset($_SESSION['anonymous_id'])) {
                $db->prepare("UPDATE cart SET user_id = ? WHERE session_id = ?")->execute([$newUserId, $_SESSION['anonymous_id']]);
            }

            setFlash('success', 'Account created successfully! Welcome aboard, ' . htmlspecialchars($fullName) . '.');
            header("Location: thank-you.php?type=welcome");
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="form-card">
        <h2 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; text-align: center; margin-bottom: 0.5rem;">Create Account</h2>
        <p style="color: var(--text-secondary); text-align: center; margin-bottom: 2rem;">Join NextGen to track orders & personalize your AI shopping recommendations</p>

        <?php if ($msg = getFlash('error')): ?>
            <div class="flash-alert error mb-4"><?= $msg ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="9876543210">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
            </div>

            <div class="form-group mb-4">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                Create Account <i class="fa-solid fa-user-plus"></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-secondary);">
            Already registered? <a href="login.php" style="color: var(--primary); font-weight: 600;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
