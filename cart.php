<?php
session_start();
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'remove':
            $id = intval($_GET['id']);
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $id) {
                    unset($_SESSION['cart'][$key]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                    break;
                }
            }
            header("Location: cart.php?removed=1");
            exit;

        case 'clear':
            $_SESSION['cart'] = [];
            header("Location: cart.php?cleared=1");
            exit;

        case 'update':
            if (isset($_POST['quantities'])) {
                foreach ($_POST['quantities'] as $id => $qty) {
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['id'] == $id) {
                            $item['quantity'] = max(1, intval($qty));
                            break;
                        }
                    }
                }
            }
            header("Location: cart.php?updated=1");
            exit;
    }
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex flex-col bg-gradient-to-br from-orange-50 via-amber-100 to-rose-100">
    <main class="flex-grow flex flex-col items-center py-10">
        <div class="w-full max-w-5xl bg-white/70 backdrop-blur-lg shadow-xl rounded-2xl p-6">
            <h1 class="text-3xl font-bold text-amber-800 mb-6 text-center">🛒 Your Cart</h1>

            <!-- Alert messages -->
            <?php if (isset($_GET['success']) && isset($_GET['order_id'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center">
                    ✅ Order #<?= intval($_GET['order_id']); ?> placed successfully!
                </div>
            <?php elseif (isset($_GET['removed'])): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 text-center">
                    ⚠️ Item removed from cart.
                </div>
            <?php elseif (isset($_GET['cleared'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-center">
                    🗑️ Cart cleared successfully.
                </div>
            <?php elseif (isset($_GET['updated'])): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4 text-center">
                    💾 Cart updated successfully.
                </div>
            <?php elseif (empty($_SESSION['cart'])): ?>
                <div class="bg-amber-50 border border-amber-400 text-amber-700 px-4 py-3 rounded text-center">
                    🍽️ Your cart is empty. <a href="index.php" class="font-semibold underline text-amber-700">Browse dishes</a>.
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="POST" action="cart.php?action=update">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-center bg-white rounded-lg shadow-sm">
                            <thead class="bg-amber-600 text-white">
                                <tr>
                                    <th class="py-3 px-4">Image</th>
                                    <th class="py-3 px-4">Dish Name</th>
                                    <th class="py-3 px-4">Price</th>
                                    <th class="py-3 px-4">Quantity</th>
                                    <th class="py-3 px-4">Subtotal</th>
                                    <th class="py-3 px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                    <tr class="border-b hover:bg-amber-50 transition">
                                        <td class="py-3 px-4">
                                            <img src="<?= htmlspecialchars($item['image']); ?>" class="w-16 h-16 rounded-lg object-cover mx-auto shadow-sm">
                                        </td>
                                        <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($item['name']); ?></td>
                                        <td class="py-3 px-4 text-amber-700">$<?= number_format($item['price'], 2); ?></td>
                                        <td class="py-3 px-4">
                                            <input type="number" name="quantities[<?= $item['id']; ?>]" value="<?= $item['quantity']; ?>" min="1"
                                                class="w-20 border rounded-lg px-2 py-1 text-center focus:ring-amber-400 focus:outline-none">
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-green-700">$<?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        <td class="py-3 px-4">
                                            <a href="cart.php?action=remove&id=<?= $item['id']; ?>"
                                               class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg shadow transition">
                                               Remove
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
                        <a href="cart.php?action=clear" class="bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-4 py-2 rounded-lg transition">
                            🗑️ Clear Cart
                        </a>

                        <div class="text-right">
                            <h3 class="text-2xl font-bold text-amber-800 mb-3">Total: $<?= number_format($total, 2); ?></h3>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition">
                                    💾 Update Cart
                                </button>
                                <a href="checkout.php"
                                   class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-semibold shadow-md transition">
                                    ✅ Checkout
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 py-4 border-t border-indigo-600/30 text-center">
        <p>© <?= date('Y'); ?> <span class="text-indigo-400 font-semibold">VOID</span>. All Rights Reserved.</p>
    </footer>
</body>
</html>
