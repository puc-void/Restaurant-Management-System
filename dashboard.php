<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$orders_query = $conn->prepare("
    SELECT o.*, SUM(oi.price * oi.quantity) as total_amount
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$orders = $orders_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gradient-to-b from-gray-50 via-white to-gray-100 min-h-screen">
  
    <nav class="fixed w-full top-0 z-50 backdrop-blur-md bg-white/80 border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="text-2xl font-extrabold bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent flex items-center gap-2">
                    Restaurant Management System
                </a>
                <div class="flex items-center space-x-3">
                    <span class="text-gray-700 font-medium hidden sm:inline">Hi, <?= htmlspecialchars($user['name']); ?></span>
                    <a href="logout.php"
                       class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-4 py-2 rounded-full text-sm font-semibold hover:opacity-90 transition-all">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-16">
        <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 mb-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">👤 My Profile</h2>
                    <p class="text-gray-700"><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></p>
                    <p class="text-gray-700"><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['phone'])): ?>
                        <p class="text-gray-700"><strong>Phone:</strong> <?= htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($user['address'])): ?>
                        <p class="text-gray-700"><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-center rounded-2xl px-6 py-4 shadow-md">
                    <p class="text-lg font-semibold">Total Orders</p>
                    <p class="text-3xl font-bold mt-1"><?= $orders->num_rows ?></p>
                </div>
            </div>
        </div>
        <h3 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-indigo-500 pl-3">🧾 My Orders</h3>

        <?php if ($orders->num_rows > 0): ?>
            <div class="overflow-x-auto bg-white rounded-2xl shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Order ID</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Ordered On</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-700 font-medium">#<?= $order['id'] ?></td>
                                <td class="px-6 py-4 text-gray-800 font-semibold">$<?= number_format($order['total_amount'], 2) ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                        $statusColor = match(strtolower($order['status'])) {
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                                        <?= ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="order_details.php?order_id=<?= $order['id'] ?>"
                                       class="inline-block bg-indigo-500 text-white px-4 py-2 rounded-full text-sm font-semibold hover:bg-indigo-600 transition-all">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white text-center py-10 rounded-2xl shadow-md">
                <p class="text-gray-500 text-lg">You have not placed any orders yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="mt-16 bg-gray-900 text-gray-400 py-2  border-t border-indigo-600/30 text-center">
        <p>© <?= date('Y'); ?> <span class="text-indigo-400 font-semibold">TAHMIDA</span>. All Rights Reserved.</p>
    </footer>
</body>
</html>
