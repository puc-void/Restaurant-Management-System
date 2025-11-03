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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-cover bg-center flex items-center justify-center"
      style="background-image: url('https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=1500&q=80');">

    <div class="backdrop-blur-md bg-white/70 border border-white/30 shadow-2xl rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-blue-800 mb-6">Admin Login</h2>

        <?php if ($login_error): ?>
            <div class="mb-4 text-red-700 bg-red-100 border border-red-300 rounded-lg p-3 text-center">
                <?= htmlspecialchars($login_error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" placeholder="Enter your email"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="password" placeholder="Enter your password"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">Don't have an admin account?</p>
            <a href="../create_admin.php" class="text-indigo-600 font-medium hover:underline">
                Create one here
            </a>
        </div>
    </div>
</body>
</html>
