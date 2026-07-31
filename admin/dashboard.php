<?php
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/header.php';

// Stats Calculations
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$totalFoods = $conn->query("SELECT COUNT(*) AS total FROM foods")->fetch_assoc()['total'] ?? 0;
$totalRestaurants = $conn->query("SELECT COUNT(*) AS total FROM restaurants")->fetch_assoc()['total'] ?? 0;

$totalRevenue = $conn->query("SELECT SUM(total) AS sum FROM orders WHERE status != 'Cancelled'")->fetch_assoc()['sum'] ?? 0.00;

$totalDelivered = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Delivered'")->fetch_assoc()['total'] ?? 0;
$totalCanceled = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Cancelled'")->fetch_assoc()['total'] ?? 0;
$totalPreparing = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Preparing'")->fetch_assoc()['total'] ?? 0;
$totalPending = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;

// Fetch 5 Recent Orders
$recentOrders = $conn->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
?>

<div class="space-y-8">

    <!-- Header Greeting -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading">
                Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?> 👋
            </h1>
            <p class="text-xs text-base-content/70">Overview of system metrics, revenue, and recent customer activities</p>
        </div>
        <div class="flex gap-2">
            <a href="manage_orders.php" class="btn btn-primary btn-sm gap-2 shadow">
                <i class="fa-solid fa-box"></i> Manage Orders
            </a>
            <a href="add_food.php" class="btn btn-secondary btn-sm gap-2 shadow">
                <i class="fa-solid fa-plus"></i> Add Dish
            </a>
        </div>
    </div>

    <!-- Metric Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stats bg-base-100 shadow border border-base-200">
            <div class="stat">
                <div class="stat-figure text-primary text-2xl"><i class="fa-solid fa-dollar-sign"></i></div>
                <div class="stat-title text-xs font-bold uppercase">Total Revenue</div>
                <div class="stat-value text-primary font-heading text-2xl">$<?= number_format($totalRevenue, 2); ?></div>
                <div class="stat-desc">From completed orders</div>
            </div>
        </div>

        <div class="stats bg-base-100 shadow border border-base-200">
            <div class="stat">
                <div class="stat-figure text-secondary text-2xl"><i class="fa-solid fa-bag-shopping"></i></div>
                <div class="stat-title text-xs font-bold uppercase">Total Orders</div>
                <div class="stat-value text-secondary font-heading text-2xl"><?= $totalOrders; ?></div>
                <div class="stat-desc"><?= $totalDelivered; ?> Delivered</div>
            </div>
        </div>

        <div class="stats bg-base-100 shadow border border-base-200">
            <div class="stat">
                <div class="stat-figure text-accent text-2xl"><i class="fa-solid fa-burger"></i></div>
                <div class="stat-title text-xs font-bold uppercase">Dishes / Items</div>
                <div class="stat-value text-accent font-heading text-2xl"><?= $totalFoods; ?></div>
                <div class="stat-desc">Across <?= $totalRestaurants; ?> Restaurants</div>
            </div>
        </div>

        <div class="stats bg-base-100 shadow border border-base-200">
            <div class="stat">
                <div class="stat-figure text-info text-2xl"><i class="fa-solid fa-users"></i></div>
                <div class="stat-title text-xs font-bold uppercase">Registered Users</div>
                <div class="stat-value text-info font-heading text-2xl"><?= $totalUsers; ?></div>
                <div class="stat-desc">Customer accounts</div>
            </div>
        </div>
    </div>

    <!-- Order Status Cards Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="manage_orders.php?status=Pending" class="card bg-warning/10 border border-warning/30 p-4 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-warning uppercase">Pending</span>
                <span class="text-2xl font-extrabold text-warning font-heading"><?= $totalPending; ?></span>
            </div>
        </a>
        <a href="manage_orders.php?status=Preparing" class="card bg-info/10 border border-info/30 p-4 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-info uppercase">Preparing</span>
                <span class="text-2xl font-extrabold text-info font-heading"><?= $totalPreparing; ?></span>
            </div>
        </a>
        <a href="manage_orders.php?status=Delivered" class="card bg-success/10 border border-success/30 p-4 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-success uppercase">Delivered</span>
                <span class="text-2xl font-extrabold text-success font-heading"><?= $totalDelivered; ?></span>
            </div>
        </a>
        <a href="manage_orders.php?status=Cancelled" class="card bg-error/10 border border-error/30 p-4 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-error uppercase">Cancelled</span>
                <span class="text-2xl font-extrabold text-error font-heading"><?= $totalCanceled; ?></span>
            </div>
        </a>
    </div>

    <!-- Recent Orders Table -->
    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <div class="p-4 bg-base-200 border-b border-base-300 font-bold text-sm flex justify-between items-center">
            <span><i class="fa-solid fa-clock-rotate-left mr-2 text-primary"></i> Recent Customer Orders</span>
            <a href="manage_orders.php" class="btn btn-ghost btn-xs text-primary">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-xs">
                <thead>
                    <tr class="font-bold uppercase">
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders->num_rows > 0): ?>
                        <?php while ($ord = $recentOrders->fetch_assoc()): ?>
                            <?php
                                $badge = match(strtolower($ord['status'])) {
                                    'pending' => 'badge-warning',
                                    'preparing' => 'badge-info',
                                    'out for delivery' => 'badge-primary',
                                    'delivered' => 'badge-success',
                                    'cancelled' => 'badge-error',
                                    default => 'badge-ghost'
                                };
                            ?>
                            <tr class="hover">
                                <td class="font-bold">#<?= $ord['id']; ?></td>
                                <td class="font-medium"><?= htmlspecialchars($ord['user_name'] ?? 'Guest'); ?></td>
                                <td class="text-base-content/70"><?= date('d M Y, H:i', strtotime($ord['created_at'])); ?></td>
                                <td class="font-extrabold text-primary">$<?= number_format($ord['total'], 2); ?></td>
                                <td><span class="badge badge-sm badge-ghost"><?= htmlspecialchars($ord['payment_method']); ?></span></td>
                                <td><span class="badge <?= $badge; ?> font-bold text-[10px]"><?= htmlspecialchars($ord['status']); ?></span></td>
                                <td class="text-right">
                                    <a href="order_details.php?id=<?= $ord['id']; ?>" class="btn btn-ghost btn-xs text-primary">
                                        <i class="fa-solid fa-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-base-content/60 py-4">No recent orders.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
