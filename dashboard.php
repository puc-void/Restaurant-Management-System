<?php
$pageTitle = "User Dashboard - GourmetHub";
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Re-Order Action
if (isset($_POST['reorder_id'])) {
    $reorder_id = intval($_POST['reorder_id']);
    $stmt_ro = $conn->prepare("
        SELECT oi.*, f.name, f.price, f.image_url 
        FROM order_items oi 
        JOIN foods f ON oi.food_id = f.id 
        WHERE oi.order_id = ?
    ");
    $stmt_ro->bind_param("i", $reorder_id);
    $stmt_ro->execute();
    $ro_items = $stmt_ro->get_result();

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    while ($item = $ro_items->fetch_assoc()) {
        $found = false;
        foreach ($_SESSION['cart'] as &$c_item) {
            if ($c_item['id'] == $item['food_id']) {
                $c_item['quantity'] += $item['quantity'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $item['food_id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'image' => $item['image_url'],
                'quantity' => $item['quantity']
            ];
        }
    }
    header("Location: cart.php?reordered=1");
    exit;
}

$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

$orders_query = $conn->prepare("
    SELECT o.*, COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$orders = $orders_query->get_result();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10 max-w-5xl space-y-10">

    <!-- Profile Header Banner -->
    <div class="card bg-base-100 shadow-xl border border-base-200 p-6 md:p-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="avatar">
                    <div class="w-20 h-20 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']); ?>&background=0D9488&color=fff&size=128" />
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold font-heading"><?= htmlspecialchars($user['name']); ?></h1>
                    <p class="text-xs text-base-content/60"><i class="fa-solid fa-envelope text-primary mr-1"></i> <?= htmlspecialchars($user['email']); ?></p>
                    <?php if (!empty($user['phone'])): ?>
                        <p class="text-xs text-base-content/60"><i class="fa-solid fa-phone text-secondary mr-1"></i> <?= htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="profile.php" class="btn btn-outline btn-sm gap-2">
                    <i class="fa-solid fa-user-pen"></i> Edit Profile
                </a>
                <div class="stats bg-primary text-primary-content shadow">
                    <div class="stat p-3 text-center">
                        <div class="stat-title text-primary-content/80 text-xs font-semibold">Total Orders</div>
                        <div class="stat-value text-2xl font-heading"><?= $orders->num_rows; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order History Section -->
    <section class="space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold font-heading flex items-center gap-2">
                <i class="fa-solid fa-receipt text-primary"></i> Order History
            </h2>
        </div>

        <?php if ($orders->num_rows > 0): ?>
            <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full text-xs">
                        <thead>
                            <tr class="bg-base-200 font-bold uppercase">
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
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
                                    <td class="font-bold text-sm">#<?= $order['id']; ?></td>
                                    <td class="text-base-content/70"><?= date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-sm badge-ghost"><?= htmlspecialchars($order['payment_method']); ?></span>
                                    </td>
                                    <td class="font-extrabold text-sm text-primary">$<?= number_format($order['total'], 2); ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass; ?> font-bold text-xs"><?= htmlspecialchars($order['status']); ?></span>
                                    </td>
                                    <td class="text-right flex items-center justify-end gap-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="reorder_id" value="<?= $order['id']; ?>">
                                            <button type="submit" class="btn btn-ghost btn-xs text-secondary gap-1" title="Re-order these items">
                                                <i class="fa-solid fa-rotate-right"></i> Re-Order
                                            </button>
                                        </form>
                                        <a href="order_details.php?order_id=<?= $order['id']; ?>" class="btn btn-primary btn-xs gap-1">
                                            <i class="fa-solid fa-eye"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card bg-base-100 shadow-md border border-base-200 p-10 text-center space-y-3">
                <i class="fa-solid fa-box-open text-4xl text-base-content/30"></i>
                <h3 class="font-bold text-lg">No Orders Placed Yet</h3>
                <p class="text-xs text-base-content/60">Start exploring delicious dishes from our top restaurants!</p>
                <div>
                    <a href="index.php" class="btn btn-primary btn-sm">Explore Menu</a>
                </div>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
