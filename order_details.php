<?php
$pageTitle = "Order Details - GourmetHub";
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
    header("Location: dashboard.php");
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_query = $conn->prepare("
    SELECT oi.*, f.name AS food_name, f.image_url, r.name AS restaurant_name
    FROM order_items oi
    JOIN foods f ON oi.food_id = f.id
    LEFT JOIN restaurants r ON f.restaurant_id = r.id
    WHERE oi.order_id = ?
");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items = $items_query->get_result();

require_once 'includes/header.php';

// Map status to step index
$statusIndex = match(strtolower($order['status'])) {
    'pending' => 1,
    'preparing' => 2,
    'out for delivery' => 3,
    'delivered' => 4,
    'cancelled' => -1,
    default => 1
};
?>

<div class="container mx-auto px-4 py-10 max-w-4xl space-y-8">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="text-xs text-base-content/60 font-semibold uppercase">Order Tracking</div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                Order #<?= $order['id']; ?>
            </h1>
            <p class="text-xs text-base-content/70">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Order Status Step Bar -->
    <div class="card bg-base-100 shadow-xl border border-base-200 p-6 md:p-8">
        <h3 class="text-lg font-bold font-heading mb-6 flex items-center gap-2">
            <i class="fa-solid fa-truck-ramp-box text-primary"></i> Live Delivery Progress
        </h3>

        <?php if ($statusIndex === -1): ?>
            <div class="alert alert-error shadow-sm text-white">
                <i class="fa-solid fa-circle-xmark text-xl"></i>
                <div>
                    <h4 class="font-bold">This Order Was Cancelled</h4>
                    <span class="text-xs">If you have questions, please contact our support team.</span>
                </div>
            </div>
        <?php else: ?>
            <ul class="steps steps-vertical md:steps-horizontal w-full font-heading font-semibold text-xs sm:text-sm">
                <li class="step <?= $statusIndex >= 1 ? 'step-primary' : ''; ?>" data-content="1">Order Placed</li>
                <li class="step <?= $statusIndex >= 2 ? 'step-primary' : ''; ?>" data-content="2">Preparing Food</li>
                <li class="step <?= $statusIndex >= 3 ? 'step-primary' : ''; ?>" data-content="3">Out for Delivery</li>
                <li class="step <?= $statusIndex >= 4 ? 'step-primary' : ''; ?>" data-content="4">Delivered</li>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Order Items & Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Items Table -->
        <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="p-4 bg-base-200 border-b border-base-300 font-bold text-sm flex items-center justify-between">
                <span>Ordered Items</span>
                <span class="badge badge-neutral"><?= $items->num_rows; ?> Items</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full text-xs">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $calc_total = 0; ?>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <?php $subtotal = $item['price'] * $item['quantity']; ?>
                            <?php $calc_total += $subtotal; ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($item['image_url']); ?>" class="w-10 h-10 rounded-lg object-cover">
                                        <div>
                                            <div class="font-bold text-sm"><?= htmlspecialchars($item['food_name']); ?></div>
                                            <div class="text-base-content/60"><?= htmlspecialchars($item['restaurant_name'] ?? ''); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-bold text-center"><?= $item['quantity']; ?></td>
                                <td>$<?= number_format($item['price'], 2); ?></td>
                                <td class="font-bold text-primary text-right">$<?= number_format($subtotal, 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary & Shipping Info -->
        <div class="space-y-6">
            <div class="card bg-base-100 shadow-xl border border-base-200 p-6 space-y-4 text-xs">
                <h3 class="font-bold text-sm font-heading border-b border-base-200 pb-2">Order Information</h3>
                
                <div>
                    <span class="text-base-content/60 block font-semibold">Current Status</span>
                    <span class="badge badge-lg <?= $statusIndex === 4 ? 'badge-success' : ($statusIndex === -1 ? 'badge-error' : 'badge-primary'); ?> font-bold mt-1">
                        <?= htmlspecialchars($order['status']); ?>
                    </span>
                </div>

                <div>
                    <span class="text-base-content/60 block font-semibold">Payment Method</span>
                    <span class="font-bold text-base-content"><?= htmlspecialchars($order['payment_method']); ?></span>
                    <?php if (!empty($order['payment_number'])): ?>
                        <span class="block text-base-content/60">(<?= htmlspecialchars($order['payment_number']); ?>)</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($order['shipping_address'])): ?>
                    <div>
                        <span class="text-base-content/60 block font-semibold">Delivery Address</span>
                        <span class="font-medium text-base-content/80"><?= htmlspecialchars($order['shipping_address']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="border-t border-base-200 pt-3 flex justify-between items-center text-sm font-bold">
                    <span>Total Paid:</span>
                    <span class="text-primary text-lg">$<?= number_format($order['total'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
