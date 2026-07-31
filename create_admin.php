<?php
session_start();
require_once __DIR__ . '/includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($name && $email && $password) {
        $password_plain = $password;
        $password_md5 = md5($password);
        $created_at = date("Y-m-d H:i:s");

        try {
            $stmt = $conn->prepare("INSERT INTO admins (name, email, password_plain, password_md5, created_at) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $email, $password_plain, $password_md5, $created_at);
                $stmt->execute();
                $success = "Admin account created successfully! <a href='admin/login.php' class='underline font-bold'>Login here</a>";
            } else {
                $error = "Prepare failed: " . $conn->error;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Email already exists. Please use another email.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="emerald">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - GourmetHub</title>
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
            <div class="w-14 h-14 rounded-2xl bg-secondary text-secondary-content flex items-center justify-center text-2xl mx-auto shadow-md">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h1 class="text-2xl font-bold font-heading">Register New Admin</h1>
            <p class="text-xs text-base-content/60">Create administrator credentials</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success shadow-md text-xs">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span><?= $success; ?></span>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error text-white shadow-md text-xs">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Admin Full Name</label>
                <div class="relative">
                    <input type="text" name="name" required placeholder="System Admin" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-user absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Admin Email</label>
                <div class="relative">
                    <input type="email" name="email" required placeholder="admin@restaurant.com" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Password</label>
                <div class="relative">
                    <input type="password" name="password" required placeholder="••••••••" class="input input-bordered w-full text-sm pl-10" />
                    <i class="fa-solid fa-lock absolute left-3.5 top-3.5 text-base-content/40"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-secondary btn-block shadow-lg gap-2 mt-4">
                <i class="fa-solid fa-user-plus"></i> Create Admin Account
            </button>
        </form>

        <div class="text-center text-xs text-base-content/60 pt-2 border-t border-base-200">
            Already have admin credentials? <a href="admin/login.php" class="link link-primary font-semibold">Admin Login</a>
        </div>
    </div>

</body>
</html>
