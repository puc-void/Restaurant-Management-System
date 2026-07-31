<?php
$pageTitle = "Shopping Cart - GourmetHub";
require_once 'includes/config.php';

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

$total = get_cart_total();
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10 max-w-5xl">
    
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-cart-shopping text-primary"></i> Your Cart
            </h1>
            <p class="text-xs text-base-content/70">Review items in your order before checkout</p>
        </div>
        <a href="index.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Continue Shopping
        </a>
    </div>

    <!-- Alert Notifications -->
    <?php if (isset($_GET['success']) && isset($_GET['order_id'])): ?>
        <div class="alert alert-success shadow-lg mb-6">
            <i class="fa-solid fa-circle-check text-xl"></i>
            <div>
                <h3 class="font-bold">Order Placed Successfully!</h3>
                <div class="text-xs">Order <strong>#<?= intval($_GET['order_id']); ?></strong> has been received and is being prepared.</div>
            </div>
            <a href="order_details.php?order_id=<?= intval($_GET['order_id']); ?>" class="btn btn-sm btn-ghost">Track Order</a>
        </div>
    <?php elseif (isset($_GET['removed'])): ?>
        <div class="alert alert-warning shadow-md mb-6">
            <i class="fa-solid fa-trash-can"></i> Item removed from your cart.
        </div>
    <?php elseif (isset($_GET['cleared'])): ?>
        <div class="alert alert-error text-error-content shadow-md mb-6">
            <i class="fa-solid fa-broom"></i> Cart cleared.
        </div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert alert-info shadow-md mb-6">
            <i class="fa-solid fa-arrows-rotate"></i> Cart updated successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
        <form method="POST" action="cart.php?action=update">
            <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200 text-xs font-bold uppercase">
                                <th>Item</th>
                                <th>Dish Name</th>
                                <th>Price</th>
                                <th class="text-center">Quantity</th>
                                <th>Subtotal</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <tr class="hover">
                                    <td>
                                        <div class="avatar">
                                            <div class="w-14 h-14 rounded-xl">
                                                <img src="<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" />
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-bold text-base"><?= htmlspecialchars($item['name']); ?></div>
                                    </td>
                                    <td class="font-semibold text-base-content/80">
                                        $<?= number_format($item['price'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="quantities[<?= $item['id']; ?>]" value="<?= $item['quantity']; ?>" min="1" max="50" 
                                               class="input input-bordered input-sm w-20 text-center font-bold" />
                                    </td>
                                    <td class="font-extrabold text-primary">
                                        $<?= number_format($item['price'] * $item['quantity'], 2); ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="cart.php?action=remove&id=<?= $item['id']; ?>" class="btn btn-ghost btn-circle btn-sm text-error" title="Remove Item">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cart Footer Actions & Summary -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-base-100 p-6 rounded-2xl border border-base-200 shadow-lg">
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-outline btn-sm gap-2">
                        <i class="fa-solid fa-arrows-rotate"></i> Update Cart
                    </button>
                    <a href="cart.php?action=clear" onclick="return confirm('Clear all items from your cart?');" class="btn btn-ghost btn-sm text-error gap-2">
                        <i class="fa-solid fa-trash"></i> Clear Cart
                    </a>
                </div>

                <div class="text-right w-full md:w-auto">
                    <div class="text-xs text-base-content/60 font-semibold uppercase">Total Amount</div>
                    <div class="text-3xl font-extrabold font-heading text-primary mb-4">$<?= number_format($total, 2); ?></div>
                    <a href="checkout.php" class="btn btn-primary btn-block md:btn-wide shadow-lg gap-2">
                        Proceed to Checkout <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="card bg-base-100 shadow-xl border border-base-200 text-center p-12 space-y-4">
            <div class="w-20 h-20 rounded-full bg-base-200 text-base-content/40 flex items-center justify-center text-4xl mx-auto">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
            <h2 class="text-2xl font-bold font-heading">Your Cart is Empty</h2>
            <p class="text-xs text-base-content/60 max-w-sm mx-auto">Explore delicious dishes from top local restaurants and start adding items to your cart!</p>
            <div>
                <a href="index.php" class="btn btn-primary shadow-md">Browse Menu Now</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
