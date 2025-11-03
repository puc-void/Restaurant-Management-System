<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();

if ($order_result->num_rows === 0) {
    die("Order not found or you don't have permission to view it.");
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_query = $conn->prepare("
    SELECT oi.*, f.name AS food_name, f.image_url
    FROM order_items oi
    JOIN foods f ON oi.food_id = f.id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items = $items_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?= $order['id'] ?> Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Order #<?= $order['id'] ?> Details</h2>
        <p class="mb-4">Status: <strong><?= htmlspecialchars($order['status']) ?></strong></p>
        <p class="mb-4">Ordered on: <strong><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></strong></p>

        <?php if ($items->num_rows > 0): ?>
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-indigo-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">Item</th>
                            <th class="px-6 py-3 text-left">Quantity</th>
                            <th class="px-6 py-3 text-left">Price</th>
                            <th class="px-6 py-3 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php $total = 0; ?>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <?php $subtotal = $item['price'] * $item['quantity']; ?>
                            <?php $total += $subtotal; ?>
                            <tr>
                                <td class="px-6 py-4 flex items-center gap-2">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['food_name']) ?>" class="w-12 h-12 rounded object-cover">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($item['food_name']) ?>
                                </td>
                                <td class="px-6 py-4"><?= $item['quantity'] ?></td>
                                <td class="px-6 py-4">$<?= number_format($item['price'], 2) ?></td>
                                <td class="px-6 py-4 font-semibold">$<?= number_format($subtotal, 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="font-bold bg-gray-100">
                            <td colspan="3" class="px-6 py-4 text-right">Total:</td>
                            <td class="px-6 py-4">$<?= number_format($total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500 mt-4">No items found in this order.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="inline-block mt-6 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">Back to Dashboard</a>
    </div>
</body>
</html>
