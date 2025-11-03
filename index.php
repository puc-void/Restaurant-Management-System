<?php
session_start();
require_once 'includes/config.php';

$restaurants = $conn->query("SELECT * FROM restaurants ORDER BY id DESC");
$dishes = $conn->query("
    SELECT f.*, r.name AS restaurant_name 
    FROM foods f 
    JOIN restaurants r ON f.restaurant_id = r.id
    WHERE f.is_active = 1
    ORDER BY f.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            backdrop-filter: ring-black;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-gray-50 via-white to-gray-100 min-h-screen">
    <nav class="fixed w-full top-0 z-50 backdrop-blur-md bg-white/80 border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-2xl font-extrabold bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent flex items-center gap-2">
                    Restaurant Management System
                </a>

                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative" x-data="{ open: false }">
                            <button
                                @click="open = !open"
                                class="flex items-center space-x-2 bg-indigo-50 hover:bg-indigo-100 text-gray-700 py-2 px-4 rounded-full text-sm font-medium transition-all">
                                <span><?= htmlspecialchars($_SESSION['username']); ?></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <ul x-show="open" @click.away="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 py-2 z-20">
                                <li><a href="dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Dashboard</a></li>
                                <li><a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Profile</a></li>
                                <li><a href="cart.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">🛒 View Cart</a></li>
                                <hr class="my-1 border-gray-200">
                                <li><a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php"
                            class="px-4 py-2 bg-gray-800 text-white rounded-full hover:bg-gray-700 transition-all text-sm font-medium">Login</a>
                        <a href="register.php"
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-full hover:opacity-90 transition-all text-sm font-medium">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section class="pt-32 text-center px-4">
        <h1 class="text-4xl font-extrabold bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent  items-center ">Find the Best Restaurants & Dishes</h1>
        <p class="text-gray-600 mb-8 max-w-xl mx-auto">Explore top-rated restaurants, discover delicious dishes, and satisfy your cravings with ease.</p>

        <form id="searchForm" class="max-w-xl mx-auto">
            <input id="searchInput" type="search" placeholder="Search restaurants or dishes..."
                class="w-full py-3 px-5 rounded-full border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 transition-all" />
        </form>
    </section>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-16">
        <h3 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-indigo-500 pl-3">Top Restaurants</h3>
        <div id="restaurantResults" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php while ($r = $restaurants->fetch_assoc()): ?>
                <a href="restaurant.php?id=<?= $r['id'] ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <img src="<?= !empty($r['image_url']) ? htmlspecialchars($r['image_url']) : 'https://via.placeholder.com/300x180?text=No+Image'; ?>"
                            alt="<?= htmlspecialchars($r['name']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="p-5 text-center">
                            <h6 class="font-semibold text-lg text-gray-900"><?= htmlspecialchars($r['name']); ?></h6>
                            <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars($r['address']); ?></p>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
        <h3 class="text-3xl font-bold text-gray-800 mt-16 mb-6 border-l-4 border-purple-500 pl-3">Popular Dishes</h3>
        <div id="dishResults" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php while ($d = $dishes->fetch_assoc()): ?>
                <a href="dish.php?id=<?= $d['id'] ?>" class="group">
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                        <img src="<?= !empty($d['image_url']) ? htmlspecialchars($d['image_url']) : 'https://via.placeholder.com/300x180?text=No+Image'; ?>"
                            alt="<?= htmlspecialchars($d['name']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="p-5 text-center">
                            <h6 class="font-semibold text-lg text-gray-900"><?= htmlspecialchars($d['name']); ?></h6>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($d['restaurant_name']); ?></p>
                            <p class="mt-3 text-xl font-bold text-indigo-600">$<?= number_format($d['price'], 2); ?></p>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    <footer class="mt-24 bg-gray-900 text-gray-400 py-4 border-t border-indigo-600/30 text-center">
        <p>© <?= date('Y'); ?> <span class="text-indigo-400 font-semibold">VOID</span>. All Rights Reserved.</p>
    </footer>

    <script src="assets/js/search.js" defer></script>
</body>
</html>
