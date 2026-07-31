<?php
$pageTitle = "Manage Orders";
require_once __DIR__ . '/header.php';

// Handle quick status update POST
if (isset($_POST['update_quick_status'])) {
    $ord_id = intval($_POST['order_id']);
    $n_status = $_POST['status'];
    $allowed_status = ['Pending', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    if (in_array($n_status, $allowed_status)) {
        $stmt_u = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt_u->bind_param("si", $n_status, $ord_id);
        $stmt_u->execute();
        $updated_msg = "Order #$ord_id status updated to '$n_status'!";
    }
}

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

    <?php if (isset($updated_msg)): ?>
        <div class="alert alert-success shadow-md text-xs">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= htmlspecialchars($updated_msg); ?></span>
        </div>
    <?php endif; ?>

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
                        <th class="text-right">Actions</th>
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
                                <td class="text-right flex items-center justify-end gap-1">
                                    <button type="button" onclick="status_modal_<?= $order['order_id']; ?>.showModal()" class="btn btn-ghost btn-xs text-info gap-1" title="Quick Update Status Modal">
                                        <i class="fa-solid fa-pen"></i> Status
                                    </button>
                                    <a href="order_details.php?id=<?= $order['order_id']; ?>" class="btn btn-primary btn-xs gap-1 shadow-sm">
                                        <i class="fa-solid fa-eye"></i> Details
                                    </a>

                                    <!-- Quick Order Status Update Modal -->
                                    <dialog id="status_modal_<?= $order['order_id']; ?>" class="modal text-left">
                                        <div class="modal-box bg-base-100 p-6 space-y-4">
                                            <form method="dialog">
                                                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
                                            </form>
                                            <h3 class="text-lg font-bold font-heading">Update Status for Order #<?= $order['order_id']; ?></h3>
                                            
                                            <form method="POST" class="space-y-4">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                                <div class="form-control">
                                                    <label class="label text-xs font-bold">Select New Status</label>
                                                    <select name="status" class="select select-bordered text-sm font-bold">
                                                        <?php foreach ($allowed_status as $st): ?>
                                                            <option value="<?= $st; ?>" <?= $order['status'] === $st ? 'selected' : ''; ?>><?= $st; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" name="update_quick_status" class="btn btn-primary btn-block shadow-md">
                                                    Save Status Change
                                                </button>
                                            </form>
                                        </div>
                                        <form method="dialog" class="modal-backdrop bg-neutral/60"><button>close</button></form>
                                    </dialog>
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
