<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: manage_orders.php");
    exit;
}

$order_id = intval($_GET['order_id']);

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $allowed_status = ['Pending','Processing','Ready','Delivered','Cancelled'];
    if (in_array($new_status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        header("Location: order_details.php?order_id=$order_id&updated=1");
        exit;
    }
}

// Fetch order info
$stmt = $conn->prepare("SELECT o.id AS order_id, o.total, o.status, o.created_at, u.name AS user_name, u.email AS user_email, u.phone, u.address
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$stmt_items = $conn->prepare("SELECT oi.quantity, oi.price, f.name AS food_name, f.image_url
                               FROM order_items oi
                               JOIN foods f ON oi.food_id = f.id
                               WHERE oi.order_id=?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Details #<?= $order['order_id'] ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">

<div class="container mx-auto py-8 px-4">
    <h2 class="text-3xl font-bold text-green-600 mb-6">📄 Order Details #<?= $order['order_id'] ?></h2>

    <?php if(isset($_GET['updated'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        ✅ Order status updated successfully!
    </div>
    <?php endif; ?>

    <!-- User Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4">User Info</h3>
        <ul class="space-y-2 text-gray-700">
            <li><strong>Name:</strong> <?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($order['user_email'] ?? '-') ?></li>
            <li><strong>Phone:</strong> <?= htmlspecialchars($order['phone'] ?? '-') ?></li>
            <li><strong>Address:</strong> <?= htmlspecialchars($order['address'] ?? '-') ?></li>
            <li><strong>Order Date:</strong> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></li>
        </ul>
    </div>

    <!-- Status Update -->
    <div class="bg-white rounded-lg shadow p-6 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h3 class="text-xl font-semibold">Order Status</h3>
        <form method="POST" class="flex gap-2 items-center">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="Pending" <?= $order['status']=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Processing" <?= $order['status']=='Processing'?'selected':'' ?>>Processing</option>
                <option value="Ready" <?= $order['status']=='Ready'?'selected':'' ?>>Ready</option>
                <option value="Delivered" <?= $order['status']=='Delivered'?'selected':'' ?>>Delivered</option>
                <option value="Cancelled" <?= $order['status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit" name="update_status" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">Update Status</button>
        </form>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-lg shadow p-6 mb-6 overflow-x-auto">
        <h3 class="text-xl font-semibold mb-4">Order Items</h3>
        <table class="min-w-full border-collapse">
            <thead class="bg-green-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-left">Image</th>
                    <th class="px-4 py-2 text-left">Dish Name</th>
                    <th class="px-4 py-2 text-center">Price ($)</th>
                    <th class="px-4 py-2 text-center">Quantity</th>
                    <th class="px-4 py-2 text-center">Subtotal ($)</th>
                </tr>
            </thead>
            <tbody>
            <?php $total_calc = 0; ?>
            <?php while($item = $items->fetch_assoc()): ?>
                <?php $subtotal = $item['price'] * $item['quantity']; ?>
                <?php $total_calc += $subtotal; ?>
                <tr class="border-b hover:bg-green-50 transition">
                    <td class="px-4 py-2">
                        <img src="<?= !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://via.placeholder.com/80x80?text=No+Image'; ?>" 
                             class="w-20 h-20 object-cover rounded-lg">
                    </td>
                    <td class="px-4 py-2"><?= htmlspecialchars($item['food_name']) ?></td>
                    <td class="px-4 py-2 text-center"><?= number_format($item['price'],2) ?></td>
                    <td class="px-4 py-2 text-center"><?= $item['quantity'] ?></td>
                    <td class="px-4 py-2 text-center"><?= number_format($subtotal,2) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Total -->
    <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
        <h3 class="text-xl font-semibold">Total</h3>
        <span class="text-xl font-bold text-green-600">$<?= number_format($total_calc,2) ?></span>
    </div>

    <a href="manage_orders.php" class="inline-block mt-6 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">← Back to Orders</a>
</div>

<footer class="mt-24 bg-gray-900 text-gray-400 py-4 border-t border-indigo-600/30 text-center">
        <p>© <?= date('Y'); ?> <span class="text-indigo-400 font-semibold">VOID</span>. All Rights Reserved.</p>
</footer>
</body>
</html>
