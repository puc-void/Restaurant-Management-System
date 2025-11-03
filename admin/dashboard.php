<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$totalFoods = $conn->query("SELECT COUNT(*) AS total FROM foods")->fetch_assoc()['total'] ?? 0;
$totalRestaurants = $conn->query("SELECT COUNT(*) AS total FROM restaurants")->fetch_assoc()['total'] ?? 0;

$totalDelivered = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Delivered'")->fetch_assoc()['total'] ?? 0;
$totalCanceled = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Cancelled'")->fetch_assoc()['total'] ?? 0;
$totalProcessing = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Processing'")->fetch_assoc()['total'] ?? 0;
$totalPending = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        a.card-link { display: block; }
        a.card-link:hover .card { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <aside class="bg-white shadow-lg w-64 flex-shrink-0 hidden md:flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-indigo-600 text-center">Admin Panel</h2>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="block px-4 py-2 rounded-lg bg-indigo-50 text-indigo-600 font-semibold hover:bg-indigo-100">Dashboard</a>
            <a href="manage_users.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">Manage Users</a>
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
            <h1 class="text-xl font-bold text-indigo-600">Admin Dashboard</h1>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm font-semibold">Logout</a>
        </nav>

        <main class="flex-1 p-6 md:p-10">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></h1>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

                <a href="manage_users.php" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Total Users</p>
                        <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $totalUsers ?></p>
                    </div>
                </a>

                <a href="manage_orders.php" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Total Orders</p>
                        <p class="text-3xl font-bold text-green-600 mt-2"><?= $totalOrders ?></p>
                    </div>
                </a>

                <a href="manage_foods.php" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Total Foods</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-2"><?= $totalFoods ?></p>
                    </div>
                </a>

                <a href="manage_restaurants.php" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Total Restaurants</p>
                        <p class="text-3xl font-bold text-red-600 mt-2"><?= $totalRestaurants ?></p>
                    </div>
                </a>

                <a href="manage_orders.php?status=Delivered" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Delivered Orders</p>
                        <p class="text-3xl font-bold text-green-500 mt-2"><?= $totalDelivered ?></p>
                    </div>
                </a>

                <a href="manage_orders.php?status=Processing" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Processing Orders</p>
                        <p class="text-3xl font-bold text-yellow-500 mt-2"><?= $totalProcessing ?></p>
                    </div>
                </a>

                <a href="manage_orders.php?status=Pending" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Pending Orders</p>
                        <p class="text-3xl font-bold text-indigo-500 mt-2"><?= $totalPending ?></p>
                    </div>
                </a>

                <a href="manage_orders.php?status=Cancelled" class="card-link">
                    <div class="card bg-white shadow-lg rounded-2xl p-6 text-center transition">
                        <p class="text-gray-500 font-semibold">Cancelled Orders</p>
                        <p class="text-3xl font-bold text-red-500 mt-2"><?= $totalCanceled ?></p>
                    </div>
                </a>

            </div>
        </main>
    </div>
</body>
</html>
