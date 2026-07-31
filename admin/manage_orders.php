<?php
$pageTitle = "Manage Orders";
require_once __DIR__ . '/header.php';

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT o.id AS order_id, o.total, o.status, o.payment_method, o.created_at, u.name AS user_name, u.email AS user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE 1=1 ";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?) ";
    $like_search = "%$search%";
    $params[] = $like_search;
    $params[] = $like_search;
    $params[] = $like_search;
    $types .= "sss";
}

$allowed_status = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
if (!empty($status_filter) && in_array($status_filter, $allowed_status)) {
    $sql .= " AND o.status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-boxes-packing text-primary"></i> Manage Customer Orders
            </h1>
            <p class="text-xs text-base-content/70">View, filter, and process customer food orders</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <form method="GET" class="card bg-base-100 shadow-md border border-base-200 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" placeholder="Search by Order ID, User Name, Email..." value="<?= htmlspecialchars($search); ?>" 
                       class="input input-bordered w-full text-sm pl-10" />
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-base-content/40"></i>
            </div>

            <select name="status" class="select select-bordered text-sm">
                <option value="">All Order Statuses</option>
                <?php foreach ($allowed_status as $st): ?>
                    <option value="<?= $st; ?>" <?= $status_filter === $st ? 'selected' : ''; ?>><?= $st; ?></option>
                <?php endforeach; ?>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm h-full gap-1">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="manage_orders.php" class="btn btn-ghost btn-sm h-full">Reset</a>
            </div>
        </div>
    </form>

    <!-- Orders Table -->
    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-xs">
                <thead>
                    <tr class="font-bold uppercase bg-base-200">
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Payment</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <?php
                                $badgeClass = match(strtolower($order['status'])) {
                                    'pending' => 'badge-warning',
                                    'preparing' => 'badge-info',
                                    'out for delivery' => 'badge-primary',
                                    'delivered' => 'badge-success',
                                    'cancelled' => 'badge-error',
                                    default => 'badge-ghost'
                                };
                            ?>
                            <tr class="hover">
                                <td class="font-bold text-sm">#<?= $order['order_id']; ?></td>
                                <td class="font-bold"><?= htmlspecialchars($order['user_name'] ?? 'Guest User'); ?></td>
                                <td class="text-base-content/70"><?= htmlspecialchars($order['user_email'] ?? '-'); ?></td>
                                <td><span class="badge badge-sm badge-ghost"><?= htmlspecialchars($order['payment_method']); ?></span></td>
                                <td class="font-extrabold text-sm text-primary">$<?= number_format($order['total'], 2); ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass; ?> font-bold text-xs"><?= htmlspecialchars($order['status']); ?></span>
                                </td>
                                <td class="text-base-content/70"><?= date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                <td class="text-right">
                                    <a href="order_details.php?id=<?= $order['order_id']; ?>" class="btn btn-primary btn-xs gap-1 shadow-sm">
                                        <i class="fa-solid fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-base-content/60 py-8">
                                <i class="fa-solid fa-box-open text-2xl block mb-2"></i>
                                No orders matching your criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
