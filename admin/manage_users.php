
<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Delete user if delete_id is set
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: manage_users.php");
    exit;
}
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <aside class="bg-white shadow-lg w-64 flex-shrink-0 hidden md:flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-indigo-600 text-center">Admin Panel</h2>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Dashboard</a>
            <a href="manage_users.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold hover:bg-indigo-100">Manage Users</a>
            <a href="manage_orders.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Orders</a>
            <a href="manage_foods.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Foods</a>
            <a href="manage_restaurants.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Restaurants</a>
        </nav>
        <div class="p-4">
            <a href="logout.php" class="w-full block bg-red-500 hover:bg-red-600 text-white text-center py-2 rounded-full font-semibold transition-all">Logout</a>
        </div>
    </aside>
    <div class="flex-1 flex flex-col">
        <nav class="bg-white shadow-md md:hidden flex justify-between items-center p-4">
            <h1 class="text-xl font-bold text-indigo-600">Manage Users</h1>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-semibold">Logout</a>
        </nav>

        <main class="flex-1 p-6 md:p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Users</h1>

            <div class="overflow-x-auto bg-white shadow-lg rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['phone']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($user['address']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="manage_users.php?delete_id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($users->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
