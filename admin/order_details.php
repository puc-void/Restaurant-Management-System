<?php
if (!isset($_GET['order_id']) && !isset($_GET['id'])) {
    header("Location: manage_orders.php");
    exit;
}

$order_id = intval($_GET['order_id'] ?? $_GET['id']);
$pageTitle = "Order Details #" . $order_id;
require_once __DIR__ . '/header.php';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $allowed_status = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    if (in_array($new_status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $updated_msg = "Order status updated to '" . htmlspecialchars($new_status) . "'!";
    }
}

// Fetch Order Info
$stmt = $conn->prepare("
    SELECT o.id AS order_id, o.total, o.status, o.payment_method, o.payment_number, o.shipping_address, o.created_at, 
           u.name AS user_name, u.email AS user_email, u.phone, u.address
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id=?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: manage_orders.php");
    exit;
}

// Fetch Items
$stmt_items = $conn->prepare("
    SELECT oi.quantity, oi.price, f.name AS food_name, f.image_url
    FROM order_items oi
    JOIN foods f ON oi.food_id = f.id
    WHERE oi.order_id=?
");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();

$allowed_status = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
?>

<div class="space-y-6 max-w-5xl">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                Order #<?= $order['order_id']; ?> Details
            </h1>
            <p class="text-xs text-base-content/70">Placed on <?= date('d M Y, H:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <a href="manage_orders.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <?php if (isset($updated_msg)): ?>
        <div class="alert alert-success shadow-md">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= $updated_msg; ?></span>
        </div>
    <?php endif; ?>

    <!-- Status Change Action Card -->
    <div class="card bg-base-100 shadow-xl border border-base-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase text-base-content/60 block">Current Status</span>
            <span class="badge badge-lg badge-primary font-bold mt-1 text-sm"><?= htmlspecialchars($order['status']); ?></span>
        </div>

        <form method="POST" class="flex gap-2 items-center">
            <select name="status" class="select select-bordered text-sm font-bold">
                <?php foreach ($allowed_status as $st): ?>
                    <option value="<?= $st; ?>" <?= $order['status'] === $st ? 'selected' : ''; ?>><?= $st; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" class="btn btn-primary shadow-md gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Status
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Items Table -->
        <div class="lg:col-span-2 card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
            <div class="p-4 bg-base-200 font-bold text-sm border-b border-base-300">
                Order Items Summary
            </div>
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full text-xs">
                    <thead>
                        <tr class="font-bold uppercase">
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
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
                                        <span class="font-bold"><?= htmlspecialchars($item['food_name']); ?></span>
                                    </div>
                                </td>
                                <td class="font-bold text-center"><?= $item['quantity']; ?></td>
                                <td>$<?= number_format($item['price'], 2); ?></td>
                                <td class="font-extrabold text-primary text-right">$<?= number_format($subtotal, 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-base-200 border-t border-base-300 flex justify-between items-center text-sm font-bold">
                <span>Total Amount:</span>
                <span class="text-primary text-xl font-extrabold">$<?= number_format($order['total'], 2); ?></span>
            </div>
        </div>

        <!-- Customer & Shipping Info Card -->
        <div class="card bg-base-100 shadow-xl border border-base-200 p-6 space-y-4 text-xs">
            <h3 class="font-bold text-sm font-heading border-b border-base-200 pb-2 flex items-center gap-2">
                <i class="fa-solid fa-user text-primary"></i> Customer Details
            </h3>

            <div>
                <span class="text-base-content/60 block font-semibold">Customer Name</span>
                <span class="font-bold text-base-content text-sm"><?= htmlspecialchars($order['user_name'] ?? 'Guest'); ?></span>
            </div>

            <div>
                <span class="text-base-content/60 block font-semibold">Email Address</span>
                <span class="font-medium text-base-content/80"><?= htmlspecialchars($order['user_email'] ?? '-'); ?></span>
            </div>

            <div>
                <span class="text-base-content/60 block font-semibold">Phone Number</span>
                <span class="font-medium text-base-content/80"><?= htmlspecialchars($order['phone'] ?? '-'); ?></span>
            </div>

            <div>
                <span class="text-base-content/60 block font-semibold">Payment Info</span>
                <span class="badge badge-neutral mt-1"><?= htmlspecialchars($order['payment_method']); ?></span>
                <?php if (!empty($order['payment_number'])): ?>
                    <div class="mt-1 text-base-content/70">Number: <?= htmlspecialchars($order['payment_number']); ?></div>
                <?php endif; ?>
            </div>

            <div>
                <span class="text-base-content/60 block font-semibold">Delivery Address</span>
                <p class="text-base-content/80 font-medium leading-relaxed">
                    <?= htmlspecialchars($order['shipping_address'] ?? ($order['address'] ?? '-')); ?>
                </p>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
