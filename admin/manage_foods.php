<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM foods WHERE id = $delete_id");
    exit('deleted'); // For AJAX delete
}

// Get restaurants for dropdown
$restaurants = $conn->query("SELECT id, name FROM restaurants ORDER BY name ASC");

// Handle AJAX search + filter
if (isset($_GET['ajax'])) {
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $restaurant_id = intval($_GET['restaurant_id'] ?? 0);

    $query = "
        SELECT f.*, r.name AS restaurant_name
        FROM foods f
        LEFT JOIN restaurants r ON f.restaurant_id = r.id
        WHERE (f.name LIKE '%$search%' OR r.name LIKE '%$search%')
    ";
    if ($restaurant_id > 0) {
        $query .= " AND f.restaurant_id = $restaurant_id";
    }
    $query .= " ORDER BY f.created_at DESC";

    $foods = $conn->query($query);
    if ($foods->num_rows === 0) {
        echo '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No foods found.</td></tr>';
    } else {
        while ($f = $foods->fetch_assoc()) {
            echo '<tr>
                    <td class="px-6 py-4 whitespace-nowrap">'.htmlspecialchars($f['id']).'</td>
                    <td class="px-6 py-4 whitespace-nowrap">'.htmlspecialchars($f['name']).'</td>
                    <td class="px-6 py-4 whitespace-nowrap">'.htmlspecialchars($f['restaurant_name']).'</td>
                    <td class="px-6 py-4 whitespace-nowrap">$'.number_format($f['price'],2).'</td>
                    <td class="px-6 py-4 whitespace-nowrap">'.($f['is_active'] ? 'Active' : 'Inactive').'</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <a href="edit_food.php?id='.$f['id'].'" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                        <a href="javascript:void(0);" onclick="deleteFood('.$f['id'].')" class="text-red-600 hover:text-red-900">Delete</a>
                    </td>
                </tr>';
        }
    }
    exit;
}

// Initial page load
$foods = $conn->query("
    SELECT f.*, r.name AS restaurant_name
    FROM foods f
    LEFT JOIN restaurants r ON f.restaurant_id = r.id
    ORDER BY f.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Foods</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex">

<!-- Sidebar -->
<aside class="bg-white shadow-lg w-64 flex-shrink-0 hidden md:flex flex-col">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-indigo-600 text-center">Admin Panel</h2>
    </div>
    <nav class="flex-1 p-4 space-y-2">
        <a href="dashboard.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Dashboard</a>
        <a href="manage_users.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Users</a>
        <a href="manage_orders.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Orders</a>
        <a href="manage_foods.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold hover:bg-indigo-100">Manage Foods</a>
        <a href="manage_restaurants.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Restaurants</a>
    </nav>
    <div class="p-4">
        <a href="logout.php" class="w-full block bg-red-500 hover:bg-red-600 text-white text-center py-2 rounded-full font-semibold transition-all">Logout</a>
    </div>
</aside>

<!-- Main Content -->
<div class="flex-1 flex flex-col">
<nav class="bg-white shadow-md md:hidden flex justify-between items-center p-4">
    <h1 class="text-xl font-bold text-indigo-600">Manage Foods</h1>
    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-semibold">Logout</a>
</nav>

<main class="flex-1 p-6 md:p-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manage Foods</h1>

    <!-- Add Food Button and Search + Filter -->
    <div class="flex flex-col sm:flex-row justify-between mb-4 gap-4">
        <a href="add_food.php" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-semibold">Add New Food</a>

        <input type="text" id="search" placeholder="Search by food or restaurant" class="flex-1 max-w-sm px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">

        <select id="restaurant-filter" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="0">All Restaurants</option>
            <?php while($r = $restaurants->fetch_assoc()): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Foods Table -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-xl">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restaurant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="food-list" class="bg-white divide-y divide-gray-200">
                <?php while($f = $foods->fetch_assoc()): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($f['id']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($f['name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($f['restaurant_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($f['price'], 2) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?= $f['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <a href="edit_food.php?id=<?= $f['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                        <a href="javascript:void(0);" onclick="deleteFood(<?= $f['id'] ?>)" class="text-red-600 hover:text-red-900">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($foods->num_rows === 0): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No foods found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</div>

<script src="../assets/js/update.js"></script>


</body>
</html>
