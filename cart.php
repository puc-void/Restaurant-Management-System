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
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'empty'): ?>
        <div class="alert alert-warning shadow-md mb-6">
            <i class="fa-solid fa-triangle-exclamation"></i> Your cart is empty. Please add items before checking out.
        </div>
    <?php endif; ?>

    <!-- Clear Cart DaisyUI Modal -->
    <dialog id="clear_cart_modal" class="modal">
        <div class="modal-box bg-base-100 p-6 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-error/10 text-error flex items-center justify-center text-3xl mx-auto">
                <i class="fa-solid fa-trash"></i>
            </div>
            <h3 class="text-xl font-bold font-heading">Clear Entire Cart?</h3>
            <p class="text-xs text-base-content/70">Are you sure you want to remove all items from your shopping cart? This action cannot be undone.</p>
            <div class="flex gap-3 pt-2">
                <a href="cart.php?action=clear" class="btn btn-error text-white flex-1">Yes, Clear Cart</a>
                <form method="dialog" class="flex-1"><button class="btn btn-ghost w-full">Cancel</button></form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop bg-neutral/60"><button>close</button></form>
    </dialog>

    <?php if (!empty($_SESSION['cart'])): ?>
        <!-- Cart Form -->
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
                                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-base-300">
                                                <img src="<?= !empty($item['image']) ? htmlspecialchars($item['image']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'; ?>" 
                                                     alt="<?= htmlspecialchars($item['name']); ?>" 
                                                     class="w-full h-full object-cover" />
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
                                        <button type="button" onclick="document.getElementById('remove_modal_<?= $item['id']; ?>').showModal()" class="btn btn-ghost btn-circle btn-sm text-error" title="Remove Item">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
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
                    <button type="button" onclick="document.getElementById('clear_cart_modal').showModal()" class="btn btn-ghost btn-sm text-error gap-2">
                        <i class="fa-solid fa-trash"></i> Clear Cart
                    </button>
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

        <!-- Remove Item Modals (Outside the main update form to avoid nested HTML form tags) -->
        <?php foreach ($_SESSION['cart'] as $item): ?>
            <dialog id="remove_modal_<?= $item['id']; ?>" class="modal">
                <div class="modal-box bg-base-100 p-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center text-xl mx-auto">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <h3 class="text-lg font-bold font-heading">Remove Item?</h3>
                    <p class="text-xs text-base-content/70">Are you sure you want to remove "<strong><?= htmlspecialchars($item['name']); ?></strong>" from your cart?</p>
                    <div class="flex gap-2 pt-2">
                        <a href="cart.php?action=remove&id=<?= $item['id']; ?>" class="btn btn-error btn-sm text-white flex-1">Remove Item</a>
                        <form method="dialog" class="flex-1"><button class="btn btn-ghost btn-sm w-full">Cancel</button></form>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop bg-neutral/60"><button>close</button></form>
            </dialog>
        <?php endforeach; ?>

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
