<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$user_id = intval($_GET['id']);
$pageTitle = "Edit User #" . $user_id;
require_once __DIR__ . '/header.php';

$stmt_u = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_u->bind_param("i", $user_id);
$stmt_u->execute();
$user = $stmt_u->get_result()->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php");
    exit;
}

$success_msg = '';
$error_msg = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email)) {
        $error_msg = "Name and Email are required.";
    } else {
        if ($password !== '') {
            $password_md5 = md5($password);
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, password_plain=?, password_md5=? WHERE id=?");
            $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $password, $password_md5, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
        }

        if ($stmt->execute()) {
            $success_msg = "User account updated successfully.";
            $stmt_u->execute();
            $user = $stmt_u->get_result()->fetch_assoc();
        } else {
            $error_msg = "Error updating user: " . $conn->error;
        }
    }
}
?>

<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-user-pen text-primary"></i> Edit Customer Account #<?= $user_id; ?>
            </h1>
            <p class="text-xs text-base-content/70">Update customer personal info, contact, and address</p>
        </div>
        <a href="manage_users.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <?php if ($error_msg): ?>
        <div class="alert alert-error text-white shadow-md text-xs">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error_msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <div class="alert alert-success shadow-md text-xs">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars($success_msg); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="card bg-base-100 shadow-xl border border-base-200 p-6 md:p-8 space-y-4">
        <div class="form-control">
            <label class="label text-xs font-bold">Full Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required class="input input-bordered text-sm" />
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Email Address *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required class="input input-bordered text-sm" />
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Phone Number</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+880 1700-000000" class="input input-bordered text-sm" />
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Delivery Address</label>
            <textarea name="address" rows="3" class="textarea textarea-bordered text-sm"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Reset Password (leave blank to keep current)</label>
            <input type="password" name="password" placeholder="••••••••" class="input input-bordered text-sm" />
        </div>

        <div class="pt-4 flex gap-3">
            <button type="submit" name="submit" class="btn btn-primary shadow-lg flex-1 gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Customer Profile
            </button>
            <a href="manage_users.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>