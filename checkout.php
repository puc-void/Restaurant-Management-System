<?php
session_start();
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

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
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

if (isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';
    $payment_number = $_POST['payment_number'] ?? null;

    $stmt_order = $conn->prepare("INSERT INTO orders (user_id, total, status, payment_method, payment_number) VALUES (?, ?, 'Pending', ?, ?)");
    $stmt_order->bind_param("idss", $user_id, $total, $payment_method, $payment_number);
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="min-h-screen bg-cover bg-center bg-no-repeat"
    style="background-image: url('https://images.unsplash.com/photo-1600891964599-f61ba0e24092?auto=format&fit=crop&w=1600&q=80');">

    <div class="bg-black bg-opacity-70 min-h-screen flex items-center justify-center">
        <div class="max-w-5xl w-full mx-auto bg-white bg-opacity-90 rounded-2xl shadow-2xl p-8">
            <h1 class="text-3xl font-bold text-center text-green-600 mb-6">Payment</h1>
            <h2 class="text-xl font-semibold mb-4 text-gray-800 text-center">Order Summary</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 bg-white rounded-lg shadow-sm">
                    <thead class="bg-green-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Image</th>
                            <th class="py-3 px-4 text-left">Dish Name</th>
                            <th class="py-3 px-4 text-center">Price</th>
                            <th class="py-3 px-4 text-center">Quantity</th>
                            <th class="py-3 px-4 text-center">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr class="border-b hover:bg-green-50 transition">
                                <td class="py-3 px-4"><img src="<?= htmlspecialchars($item['image']); ?>"
                                        class="w-16 h-16 rounded-lg object-cover border"></td>
                                <td class="py-3 px-4 font-medium text-gray-800"><?= htmlspecialchars($item['name']); ?></td>
                                <td class="py-3 px-4 text-center text-gray-700">$<?= number_format($item['price'], 2); ?>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700"><?= $item['quantity']; ?></td>
                                <td class="py-3 px-4 text-center font-semibold text-green-700">
                                    $<?= number_format($item['price'] * $item['quantity'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="text-xl font-semibold text-gray-800 mt-8 mb-3">Select Payment Method</h2>

            <form method="POST" id="checkoutForm" action="checkout.php">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <label
                        class="flex items-center space-x-4 border rounded-lg p-4 cursor-pointer hover:bg-green-50 transition">
                        <input type="radio" name="payment_method" value="Cash on Delivery"
                            class="w-5 h-5 text-green-600">
                        <img src="https://png.pngtree.com/png-clipart/20230405/original/pngtree-cash-on-delivery-red-vector-logo-with-hand-giving-money-png-image_9029148.png"
                            alt="Cash On Delivery" class="w-12 h-12 object-contain">
                        <span class="text-gray-800 font-medium">Cash on Delivery</span>
                    </label>
                    <label
                        class="flex items-center space-x-4 border rounded-lg p-4 cursor-pointer hover:bg-pink-50 transition">
                        <input type="radio" name="payment_method" value="bKash" class="w-5 h-5 text-pink-600">
                        <img src="https://logos-world.net/wp-content/uploads/2024/10/Bkash-Logo-500x281.png" alt="bKash"
                            class="w-12 h-12 object-contain">
                        <span class="text-gray-800 font-medium">bKash</span>
                    </label>
                    <label
                        class="flex items-center space-x-4 border rounded-lg p-4 cursor-pointer hover:bg-orange-50 transition">
                        <input type="radio" name="payment_method" value="Nagad" class="w-5 h-5 text-orange-600">
                        <img src="https://www.logo.wine/a/logo/Nagad/Nagad-Logo.wine.svg" alt="Nagad"
                            class="w-12 h-12 object-contain">
                        <span class="text-gray-800 font-medium">Nagad</span>
                    </label>
                    <label
                        class="flex items-center space-x-4 border rounded-lg p-4 cursor-pointer hover:bg-purple-50 transition">
                        <input type="radio" name="payment_method" value="Rocket" class="w-5 h-5 text-purple-600">
                        <img src="https://images.seeklogo.com/logo-png/31/2/dutch-bangla-rocket-logo-png_seeklogo-317692.png"
                            alt="Rocket" class="w-12 h-12 object-contain">
                        <span class="text-gray-800 font-medium">Rocket</span>
                    </label>

                    <label
                        class="flex items-center space-x-4 border rounded-lg p-4 cursor-pointer hover:bg-blue-50 transition">
                        <input type="radio" name="payment_method" value="Visa" class="w-5 h-5 text-blue-600">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa"
                            class="w-12 h-12 object-contain pointer-events-none">
                        <span class="text-gray-800 font-medium">Visa</span>
                    </label>

                    <label
                        class="flex items-center space-x-4 border rounded-lg p-4 cursor-pointer hover:bg-yellow-50 transition">
                        <input type="radio" name="payment_method" value="MasterCard" class="w-5 h-5 text-yellow-600">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg"
                            alt="MasterCard" class="w-12 h-12 object-contain pointer-events-none">
                        <span class="text-gray-800 font-medium">MasterCard</span>
                    </label>

                </div>

                <div id="paymentSection" class="hidden border border-green-300 bg-green-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Enter Payment Details</h3>
                    <div id="paymentFields"></div>
                </div>

                <div class="flex flex-col m d:flex-row justify-between items-center mt-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        Total: <span class="text-green-600">$<?= number_format($total, 2); ?></span>
                    </h3>
                    <button type="submit" name="place_order" id="placeOrder"
                        class="px-6 py-2.5 bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 transform hover:scale-105 transition">
                        Place Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/checkout.js"></script>
</body>

</html>