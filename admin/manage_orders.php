<?php
session_start();
require_once '../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$sql = "SELECT o.id AS order_id, o.total, o.status, o.created_at, u.name AS user_name, u.email AS user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE 1 ";

$params = [];
$types = "";
if (!empty($search)) {
    $sql .= " AND (o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?) ";
    $like_search = "%$search%";
    $params[] = &$like_search;
    $params[] = &$like_search;
    $params[] = &$like_search;
    $types .= "sss";
}
$allowed_status = ['Pending','Processing','Ready','Delivered','Cancelled'];
if (!empty($status_filter) && in_array($status_filter, $allowed_status)) {
    $sql .= " AND o.status = ? ";
    $params[] = &$status_filter;
    $types .= "s";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Orders</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8">
        <h2 class="text-3xl font-bold text-green-600 mb-6">📦 Manage Orders</h2>

        <!-- Filter Form -->
        <form method="GET" class="flex flex-col md:flex-row items-center gap-3 mb-6 bg-white p-4 rounded-lg shadow">
            <input type="text" name="search" placeholder="Search by Order ID, Name, Email" value="<?= htmlspecialchars($search) ?>"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">

            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                <option value="">All Status</option>
                <?php foreach ($allowed_status as $status): ?>
                    <option value="<?= $status ?>" <?= $status_filter==$status?'selected':'' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Filter</button>
                <a href="manage_orders.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">Reset</a>
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow overflow-hidden">
                <thead class="bg-green-600 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left">Order ID</th>
                        <th class="py-3 px-4 text-left">User</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-center">Total ($)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Order Date</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($order = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-green-50 transition">
                            <td class="py-3 px-4 font-medium"><?= $order['order_id'] ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($order['user_email'] ?? '-') ?></td>
                            <td class="py-3 px-4 text-center"><?= number_format($order['total'],2) ?></td>
                            <td class="py-3 px-4 text-center">
                                <?php
                                    $status_color = match($order['status']){
                                        'Pending'=>'bg-yellow-200 text-yellow-800',
                                        'Processing'=>'bg-blue-200 text-blue-800',
                                        'Ready'=>'bg-indigo-200 text-indigo-800',
                                        'Delivered'=>'bg-green-200 text-green-800',
                                        'Cancelled'=>'bg-red-200 text-red-800',
                                        default=>'bg-gray-200 text-gray-800',
                                    };
                                ?>
                                <span class="px-2 py-1 rounded-full text-sm font-semibold <?= $status_color ?>"><?= $order['status'] ?></span>
                            </td>
                            <td class="py-3 px-4 text-center"><?= $order['created_at'] ?></td>
                            <td class="py-3 px-4 text-center">
                                <a href="order_details.php?order_id=<?= $order['order_id'] ?>" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">No orders found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <footer class="mt-48 bg-gray-900 text-gray-400 py-4  border-t border-indigo-900/30 text-center">
        <p>© <?= date('Y'); ?> <span class="text-indigo-400 font-semibold">VOID</span>. All Rights Reserved.</p>
    </footer>
</body>
</html>
