<?php
$pageTitle = "My Profile - GourmetHub";
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = null;
$error = null;

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!empty($name)) {
        $stmt_u = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt_u->bind_param("sssi", $name, $phone, $address, $user_id);
        if ($stmt_u->execute()) {
            $_SESSION['username'] = $name;
            $msg = "Profile information updated successfully!";
        } else {
            $error = "Failed to update profile.";
        }
    } else {
        $error = "Name cannot be empty.";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    
    if (strlen($new_pass) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $stmt_p = $conn->prepare("SELECT password_plain FROM users WHERE id = ?");
        $stmt_p->bind_param("i", $user_id);
        $stmt_p->execute();
        $curr = $stmt_p->get_result()->fetch_assoc();

        if ($curr && $curr['password_plain'] === $current_pass) {
            $md5 = md5($new_pass);
            $stmt_up = $conn->prepare("UPDATE users SET password_plain = ?, password_md5 = ? WHERE id = ?");
            $stmt_up->bind_param("ssi", $new_pass, $md5, $user_id);
            $stmt_up->execute();
            $msg = "Password changed successfully!";
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Fetch user info
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10 max-w-4xl space-y-8">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-user-gear text-primary"></i> Account Settings
            </h1>
            <p class="text-xs text-base-content/70">Manage your profile details and delivery address</p>
        </div>
        <a href="dashboard.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($msg): ?>
        <div class="alert alert-success shadow-md">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= htmlspecialchars($msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error text-white shadow-md">
            <i class="fa-solid fa-circle-exclamation text-lg"></i>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Personal Information Form -->
        <form method="POST" class="card bg-base-100 shadow-xl border border-base-200 p-6 space-y-4">
            <h3 class="text-lg font-bold font-heading border-b border-base-200 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-address-card text-secondary"></i> Personal Information
            </h3>

            <div class="form-control">
                <label class="label text-xs font-bold">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required class="input input-bordered text-sm" />
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Email Address</label>
                <input type="email" value="<?= htmlspecialchars($user['email']); ?>" disabled class="input input-bordered bg-base-200 text-sm opacity-70" />
                <span class="text-[10px] text-base-content/50 mt-1">Email address cannot be changed.</span>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+880 1700-000000" class="input input-bordered text-sm" />
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Default Delivery Address</label>
                <textarea name="address" rows="3" placeholder="Enter house no, street, city..." class="textarea textarea-bordered text-sm"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <button type="submit" name="update_profile" class="btn btn-primary shadow-md gap-2 mt-2">
                <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
            </button>
        </form>

        <!-- Security & Password Form -->
        <form method="POST" class="card bg-base-100 shadow-xl border border-base-200 p-6 space-y-4">
            <h3 class="text-lg font-bold font-heading border-b border-base-200 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-lock text-accent"></i> Security & Password
            </h3>

            <div class="form-control">
                <label class="label text-xs font-bold">Current Password</label>
                <input type="password" name="current_password" required placeholder="••••••••" class="input input-bordered text-sm" />
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">New Password</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Min 6 characters" class="input input-bordered text-sm" />
            </div>

            <div class="pt-4">
                <button type="submit" name="change_password" class="btn btn-accent text-accent-content shadow-md gap-2 w-full">
                    <i class="fa-solid fa-key"></i> Update Password
                </button>
            </div>
        </form>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
