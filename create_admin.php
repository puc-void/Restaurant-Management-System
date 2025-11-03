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
        if (!$conn) {
            die("Database connection failed!");
        }
        $password_plain = $password;
        $password_md5 = md5($password);
        $created_at = date("Y-m-d H:i:s");

        try {
            $stmt = $conn->prepare("INSERT INTO admins (name, email, password_plain, password_md5, created_at) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $email, $password_plain, $password_md5, $created_at);
                $stmt->execute();
                $success = "Admin account created successfully!";
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-cover bg-center flex items-center justify-center" 
      style="background-image: url('https://images.unsplash.com/photo-1551218808-94e220e084d2?auto=format&fit=crop&w=1500&q=80');">

    <div class="backdrop-blur-md bg-white/15 shadow-2xl rounded-2xl p-8 w-full max-w-md border border-white/30">
        <h2 class="text-3xl font-bold text-center text-yellow-800 mb-1">Create Admin</h2>

        <?php if ($success): ?>
            <div class="mb-4 text-green-700 bg-green-100 border border-green-300 rounded-lg p-3 text-center">
                <?php echo $success; ?>
            </div>
        <?php elseif ($error): ?>
            <div class="mb-4 text-red-700 bg-red-100 border border-red-300 rounded-lg p-3 text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Full Name</label>
                <input type="text" name="name" placeholder="Enter full name"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Email Address</label>
                <input type="email" name="email" placeholder="Enter email"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="password" placeholder="Enter password"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                Create Admin
            </button>
        </form>
    </div>

</body>

</html>
