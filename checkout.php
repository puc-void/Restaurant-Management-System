<?php
$pageTitle = "Checkout - GourmetHub";
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
if (empty($_SESSION['cart'])) {
    header("Location: cart.php?error=empty");
    exit;
}

$total = get_cart_total();

// Fetch user profile info for shipping address prefill
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Verification AJAX code
if (isset($_POST['action']) && $_POST['action'] === 'send_code') {
    $number = $_POST['number'];
    $code = rand(100000, 999999);
    $_SESSION['verify_code'] = $code;
    $_SESSION['verify_number'] = $number;
    echo json_encode(['success' => true, 'message' => "Verification code sent to $number", 'code' => $code]);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    $input_code = $_POST['code'];
    if (isset($_SESSION['verify_code']) && $input_code == $_SESSION['verify_code']) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    }
    exit;
}

// Place Order
if (isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';
    $payment_number = $_POST['payment_number'] ?? null;
    $shipping_address = trim($_POST['shipping_address'] ?? ($user['address'] ?? ''));

    $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total, status, payment_method, payment_number, shipping_address) VALUES (?, ?, 'Pending', ?, ?, ?)");
    $stmt_order->bind_param("idsss", $user_id, $total, $payment_method, $payment_number, $shipping_address);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($_SESSION['cart'] as $item) {
        $stmt_item->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt_item->execute();
    }

    unset($_SESSION['cart']);
    header("Location: cart.php?success=1&order_id=$order_id");
    exit;
}

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10 max-w-5xl space-y-10">

    <!-- Progress Steps -->
    <div class="flex justify-center">
        <ul class="steps steps-horizontal w-full max-w-2xl font-heading font-semibold text-xs sm:text-sm">
            <li class="step step-primary">Cart</li>
            <li class="step step-primary">Checkout & Payment</li>
            <li class="step">Order Complete</li>
        </ul>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Summary -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-base-100 shadow-xl border border-base-200 p-6 space-y-4">
                <h3 class="text-lg font-bold font-heading flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary"></i> Order Summary
                </h3>

                <div class="divide-y divide-base-200 max-h-72 overflow-y-auto pr-1">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <img src="<?= htmlspecialchars($item['image']); ?>" class="w-10 h-10 rounded-lg object-cover">
                                <div>
                                    <h4 class="font-bold line-clamp-1"><?= htmlspecialchars($item['name']); ?></h4>
                                    <span class="text-base-content/60"><?= $item['quantity']; ?> x $<?= number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                            <span class="font-bold text-primary">$<?= number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-base-300 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between text-base-content/70">
                        <span>Items Subtotal:</span>
                        <span class="font-semibold">$<?= number_format($total, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-base-content/70">
                        <span>Delivery Fee:</span>
                        <span class="font-semibold text-success">FREE</span>
                    </div>
                    <div class="divider my-1"></div>
                    <div class="flex justify-between text-base font-extrabold font-heading">
                        <span>Total Payable:</span>
                        <span class="text-primary text-xl">$<?= number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping & Payment Form -->
        <div class="lg:col-span-2">
            <form method="POST" id="checkoutForm" class="card bg-base-100 shadow-xl border border-base-200 p-6 md:p-8 space-y-6">
                <div>
                    <h2 class="text-xl font-bold font-heading mb-1 flex items-center gap-2">
                        <i class="fa-solid fa-truck text-secondary"></i> Delivery Information
                    </h2>
                    <p class="text-xs text-base-content/60 mb-4">Specify where your food should be delivered</p>
                    
                    <div class="form-control">
                        <label class="label text-xs font-bold">Delivery Address</label>
                        <textarea name="shipping_address" rows="2" required placeholder="Enter full street address, apartment, building, city..." 
                                  class="textarea textarea-bordered text-sm font-medium"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="divider my-0"></div>

                <div>
                    <h2 class="text-xl font-bold font-heading mb-1 flex items-center gap-2">
                        <i class="fa-solid fa-credit-card text-accent"></i> Select Payment Method
                    </h2>
                    <p class="text-xs text-base-content/60 mb-4">Choose your preferred payment option</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="label cursor-pointer border border-base-300 rounded-xl p-4 hover:border-primary transition flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="Cash on Delivery" checked class="radio radio-primary" />
                                <span class="font-bold text-sm">Cash on Delivery</span>
                            </div>
                            <i class="fa-solid fa-money-bill-wave text-success text-xl"></i>
                        </label>

                        <label class="label cursor-pointer border border-base-300 rounded-xl p-4 hover:border-primary transition flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="bKash" class="radio radio-secondary" />
                                <span class="font-bold text-sm">bKash</span>
                            </div>
                            <span class="badge badge-secondary font-bold">Mobile</span>
                        </label>

                        <label class="label cursor-pointer border border-base-300 rounded-xl p-4 hover:border-primary transition flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="Nagad" class="radio radio-accent" />
                                <span class="font-bold text-sm">Nagad</span>
                            </div>
                            <span class="badge badge-accent font-bold">Mobile</span>
                        </label>

                        <label class="label cursor-pointer border border-base-300 rounded-xl p-4 hover:border-primary transition flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="Rocket" class="radio radio-info" />
                                <span class="font-bold text-sm">Rocket</span>
                            </div>
                            <span class="badge badge-info font-bold">Mobile</span>
                        </label>

                        <label class="label cursor-pointer border border-base-300 rounded-xl p-4 hover:border-primary transition flex items-center justify-between sm:col-span-2">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="Visa / Card" class="radio radio-primary" />
                                <span class="font-bold text-sm">Credit / Debit Card (Visa / MasterCard)</span>
                            </div>
                            <i class="fa-solid fa-credit-card text-primary text-xl"></i>
                        </label>
                    </div>
                </div>

                <div id="paymentSection" class="hidden alert alert-info bg-base-200 border border-info/30 p-4 rounded-xl">
                    <div class="w-full space-y-2">
                        <h4 class="font-bold text-xs">Payment Account Number</h4>
                        <input type="text" name="payment_number" placeholder="Enter mobile wallet / account number" class="input input-sm input-bordered w-full" />
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-base-content/60 block">Total Amount</span>
                        <span class="text-2xl font-extrabold text-primary font-heading">$<?= number_format($total, 2); ?></span>
                    </div>
                    <button type="submit" name="place_order" class="btn btn-primary btn-lg shadow-lg gap-2">
                        <i class="fa-solid fa-circle-check"></i> Place Order Now
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $('input[name="payment_method"]').on('change', function() {
        const val = $(this).val();
        if (val !== 'Cash on Delivery') {
            $('#paymentSection').removeClass('hidden');
        } else {
            $('#paymentSection').addClass('hidden');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>