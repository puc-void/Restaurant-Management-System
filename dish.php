<?php
session_start();
require_once 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=dish.php?id=" . urlencode($_GET['id']));
    exit;
}

$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$dish_id = intval($_GET['id']);
$stmt = $conn->prepare("
    SELECT f.*, r.name AS restaurant_name 
    FROM foods f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    WHERE f.id=? AND f.is_active=1
");
$stmt->bind_param("i", $dish_id);
$stmt->execute();
$dish = $stmt->get_result()->fetch_assoc();

if (!$dish) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['add_to_cart'])) {
    $quantity = max(1, intval($_POST['quantity']));
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $dish_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $dish['id'],
            'name' => $dish['name'],
            'price' => $dish['price'],
            'image' => $dish['image_url'],
            'quantity' => $quantity
        ];
    }

    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($dish['name']); ?> - Dish Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-amber-100 via-orange-200 to-red-100 flex items-center justify-center px-4 py-10">

    <div class="max-w-5xl w-full bg-white/70 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden grid md:grid-cols-2">
        <!-- Image Section -->
        <div class="relative">
            <img src="<?= !empty($dish['image_url']) ? htmlspecialchars($dish['image_url']) : 'https://via.placeholder.com/500x400?text=No+Image'; ?>"
                 alt="<?= htmlspecialchars($dish['name']); ?>"
                 class="object-cover w-full h-full">
            <div class="absolute top-4 left-4 bg-black/60 text-white px-3 py-1 rounded-lg text-sm">
                <?= htmlspecialchars($dish['restaurant_name']); ?>
            </div>
        </div>

        <div class="p-8 flex flex-col justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($dish['name']); ?></h1>
                <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($dish['description']); ?></p>
                <p class="text-2xl font-semibold text-amber-600 mb-6">$<?= number_format($dish['price'], 2); ?></p>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="quantity" class="block text-gray-700 font-medium mb-1">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1"
                               class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-center focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" name="add_to_cart"
                                class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                            Add to Cart
                        </button>
                        <a href="index.php"
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 rounded-lg text-center transition duration-200">
                            ← Back
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-6 border-t pt-4 text-sm text-gray-500 text-center">
                © <?= date('Y'); ?> <span class="font-semibold">VOID</span>. All rights reserved.
            </div>
        </div>
    </div>

</body>
</html>
