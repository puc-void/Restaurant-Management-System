<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$user_id = intval($_GET['id']);
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    header("Location: manage_users.php");
    exit;
}

$success_msg = '';
$error_msg = '';

// Handle form submission
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
            $success_msg = "User updated successfully.";
            $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        } else {
            $error_msg = "Error updating user: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex">
    <aside class="bg-white shadow-lg w-64 flex-shrink-0 hidden md:flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-indigo-600 text-center">Admin Panel</h2>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Dashboard</a>
            <a href="manage_users.php"
                class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold hover:bg-indigo-100">Manage
                Users</a>
            <a href="manage_orders.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage
                Orders</a>
            <a href="manage_foods.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage
                Foods</a>
            <a href="manage_restaurants.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage
                Restaurants</a>
        </nav>
        <div class="p-4">
            <a href="logout.php"
                class="w-full block bg-red-500 hover:bg-red-600 text-white text-center py-2 rounded-full font-semibold transition-all">Logout</a>
        </div>
    </aside>
    <div class="flex-1 flex flex-col">
        <nav class="bg-white shadow-md md:hidden flex justify-between items-center p-4">
            <h1 class="text-xl font-bold text-indigo-600">Edit User</h1>
            <a href="logout.php"
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-semibold">Logout</a>
        </nav>

        <main class="flex-1 p-6 md:p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Edit User</h1>

            <?php if ($success_msg): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success_msg) ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error_msg) ?></div>
            <?php endif; ?>

            <form method="POST" class="bg-white p-6 rounded-xl shadow-lg max-w-lg">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Address</label>
                    <textarea name="address"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password (leave blank to keep current)</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-between items-center">
                    <a href="manage_users.php"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded font-semibold">Cancel</a>
                    <button type="submit" name="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-semibold">Update</button>
                </div>
            </form>
        </main>
    </div>

</body>

</html>