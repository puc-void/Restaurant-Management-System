<?php
session_start();
require_once 'includes/config.php';
$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Password verification
            if (
                (!empty($user['password_hash']) && password_verify($password, $user['password_hash'])) ||
                (!empty($user['password_md5']) && md5($password) === $user['password_md5'])
            ) {
                // Only set user session, do NOT unset admin session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                session_regenerate_id(true);

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }

        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">User Login</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-center mb-4">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">Email</label>
            <input type="email" name="email" id="email" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">Password</label>
            <input type="password" name="password" id="password" class="w-full px-4 py-2 border rounded-lg" required>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg">Login</button>
        <p class="text-center text-sm text-gray-600 mt-4">
            Don't have an account? <a href="register.php" class="text-indigo-600 hover:underline">Sign Up</a>
        </p>
    </form>
</div>
</body>
</html>
