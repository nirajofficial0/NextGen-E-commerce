<?php
/**
 * User Account & Saved Profile Management
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = Database::getConnection();
$userId = getCurrentUserId();

// Handle Profile Updates BEFORE HTML Output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $pincode = sanitizeInput($_POST['pincode']);

    $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
    $stmt->execute([$fullName, $phone, $address, $city, $state, $pincode, $userId]);

    setFlash('success', 'Profile updated successfully!');
    header("Location: profile.php");
    exit;
}

$user = getCurrentUser();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h1 class="section-title mb-4"><i class="fa-solid fa-user-gear"></i> Account Profile</h1>

    <?php if ($msg = getFlash('success')): ?>
        <div class="flash-alert success mb-4"><?= $msg ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 3rem;">
        <!-- Left: Avatar Card -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem; text-align: center; height: fit-content;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--neon-pink)); color: #fff; font-size: 3rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto;">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <h3 style="font-family: var(--font-heading); font-size: 1.3rem; margin-bottom: 0.25rem;"><?= htmlspecialchars($user['full_name']) ?></h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;"><?= htmlspecialchars($user['email']) ?></p>
            <span style="background: rgba(99, 102, 241, 0.15); color: var(--primary); font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 99px; text-transform: uppercase;">
                Role: <?= htmlspecialchars($user['role']) ?>
            </span>
        </div>

        <!-- Right: Edit Form Card -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
            <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Personal Details & Shipping Address</h3>
            
            <form action="profile.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Default Shipping Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="<?= htmlspecialchars($user['pincode'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-primary" style="margin-top: 1rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Updates
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
