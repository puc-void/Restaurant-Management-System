<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            $valid = false;
            if (!empty($admin['password_plain']) && $password === $admin['password_plain']) {
                $valid = true;
            }
            if (!empty($admin['password_md5']) && md5($password) === $admin['password_md5']) {
                $valid = true;
            }

            if ($valid) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: dashboard.php");
                exit;
            } else {
                $login_error = "Invalid email or password.";
            }
        } else {
            $login_error = "Invalid email or password.";
        }
    } else {
        $login_error = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GourmetHub</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- DaisyUI 4 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-heading { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">

    <div class="card bg-base-100 shadow-2xl border border-base-300 w-full max-w-md p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-primary text-primary-content flex items-center justify-center text-2xl mx-auto shadow-md">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading">Admin Portal Login</h1>
            <p class="text-xs text-base-content/60">System Administration & Control Center</p>
        </div>

        <?php if ($login_error): ?>
            <div class="alert alert-error text-white shadow-md text-xs">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($login_error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Admin Email</label>
                <div class="relative">
                    <input type="email" name="email" required placeholder="admin@restaurant.com" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Admin Password</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="••••••••" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block shadow-lg gap-2 mt-4">
                <i class="fa-solid fa-right-to-bracket"></i> Login to Admin
            </button>
        </form>

        <div class="text-center text-xs text-base-content/60 pt-2 border-t border-base-200">
            Need an admin account? <a href="../create_admin.php" class="link link-primary font-semibold">Create initial admin</a>
        </div>
    </div>

</body>
</html>
