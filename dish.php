<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$dish_id = intval($_GET['id']);
require_once 'includes/config.php';

$stmt = $conn->prepare("
    SELECT f.*, r.name AS restaurant_name, r.id AS restaurant_id 
    FROM foods f 
    JOIN restaurants r ON f.restaurant_id = r.id 
    WHERE f.id = ? AND f.is_active = 1
");
$stmt->bind_param("i", $dish_id);
$stmt->execute();
$dish = $stmt->get_result()->fetch_assoc();

if (!$dish) {
    header("Location: index.php");
    exit;
}

$pageTitle = htmlspecialchars($dish['name']) . " - Dish Details";
require_once 'includes/header.php';

// Handle Add to Cart
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

// Handle Submit Dish Review
if (isset($_POST['submit_dish_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=dish.php?id=" . $dish_id);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $rating = max(1, min(5, intval($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');

    $stmt_rev = $conn->prepare("INSERT INTO reviews (user_id, food_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt_rev->bind_param("iiis", $user_id, $dish_id, $rating, $comment);
    $stmt_rev->execute();
    $review_msg = "Thank you for your rating & review on this dish!";
}

// Fetch Reviews for Dish
$stmt_d_rev = $conn->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.food_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_d_rev->bind_param("i", $dish_id);
$stmt_d_rev->execute();
$reviews = $stmt_d_rev->get_result();

$food_rating = get_food_rating($conn, $dish_id);
?>

<div class="container mx-auto px-4 py-10 max-w-6xl space-y-10">

    <!-- Breadcrumb Nav -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="index.php"><i class="fa-solid fa-house text-primary mr-1"></i> Home</a></li>
            <li><a href="restaurant.php?id=<?= $dish['restaurant_id']; ?>"><?= htmlspecialchars($dish['restaurant_name']); ?></a></li>
            <li class="font-bold text-primary"><?= htmlspecialchars($dish['name']); ?></li>
        </ul>
    </div>

    <?php if (isset($review_msg)): ?>
        <div class="alert alert-success shadow-md">
            <i class="fa-solid fa-star text-lg text-warning"></i>
            <span><?= $review_msg; ?></span>
        </div>
    <?php endif; ?>

    <!-- Write Dish Review DaisyUI Modal -->
    <dialog id="dish_review_modal" class="modal">
        <div class="modal-box max-w-md bg-base-100 p-6">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
            </form>
            <h3 class="text-xl font-bold font-heading mb-4 flex items-center gap-2">
                <i class="fa-solid fa-star text-warning"></i> Rate & Review <?= htmlspecialchars($dish['name']); ?>
            </h3>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="label text-xs font-semibold">Your Star Rating</label>
                        <div class="rating rating-lg gap-1">
                            <input type="radio" name="rating" value="1" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="2" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="3" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="4" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="5" class="mask mask-star-2 bg-warning" checked />
                        </div>
                    </div>
                    <div>
                        <label class="label text-xs font-semibold">Write your Review</label>
                        <textarea name="comment" rows="3" required placeholder="How did you like the taste and portion size?" class="textarea textarea-bordered w-full text-sm"></textarea>
                    </div>
                    <button type="submit" name="submit_dish_review" class="btn btn-primary btn-block shadow-md">
                        Submit Dish Review
                    </button>
                </form>
            <?php else: ?>
                <div class="bg-base-200 p-6 rounded-xl text-center space-y-3">
                    <p class="text-xs text-base-content/70">Login to leave a review for this dish.</p>
                    <a href="login.php?redirect=dish.php?id=<?= $dish_id; ?>" class="btn btn-primary btn-sm btn-block">Login to Review</a>
                </div>
            <?php endif; ?>
        </div>
        <form method="dialog" class="modal-backdrop bg-neutral/60"><button>close</button></form>
    </dialog>

    <!-- Main Dish Card Section -->
    <div class="card lg:card-side bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <figure class="lg:w-1/2 relative min-h-[340px] bg-base-300">
            <img src="<?= !empty($dish['image_url']) ? htmlspecialchars($dish['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'; ?>" 
                 alt="<?= htmlspecialchars($dish['name']); ?>" 
                 class="w-full h-full object-cover" />
            <div class="absolute top-4 left-4">
                <span class="badge badge-accent font-semibold shadow"><?= htmlspecialchars($dish['category'] ?? 'Main Dish'); ?></span>
            </div>
        </figure>
        
        <div class="card-body lg:w-1/2 p-8 justify-between">
            <div>
                <a href="restaurant.php?id=<?= $dish['restaurant_id']; ?>" class="text-xs text-secondary font-bold uppercase tracking-wider hover:underline">
                    <i class="fa-solid fa-store mr-1"></i> <?= htmlspecialchars($dish['restaurant_name']); ?>
                </a>
                
                <h1 class="card-title text-3xl font-extrabold font-heading mt-1 mb-2">
                    <?= htmlspecialchars($dish['name']); ?>
                </h1>

                <!-- Rating Stats -->
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex text-warning text-sm">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= round($food_rating['avg']) ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="font-bold text-sm"><?= $food_rating['avg']; ?></span>
                    <span class="text-xs text-base-content/60">(<?= $food_rating['count']; ?> Customer Reviews)</span>
                    <button onclick="dish_review_modal.showModal()" class="btn btn-ghost btn-xs text-primary ml-2">
                        <i class="fa-solid fa-pen"></i> Review
                    </button>
                </div>

                <p class="text-base-content/80 text-sm leading-relaxed mb-6">
                    <?= htmlspecialchars($dish['description']); ?>
                </p>

                <div class="bg-base-200 p-4 rounded-2xl flex items-center justify-between mb-6">
                    <div>
                        <span class="text-xs text-base-content/60 block font-medium">Unit Price</span>
                        <span class="text-3xl font-extrabold text-primary font-heading">$<?= number_format($dish['price'], 2); ?></span>
                    </div>
                    <div class="badge badge-success gap-1 text-xs">
                        <i class="fa-solid fa-circle-check"></i> In Stock
                    </div>
                </div>

                <!-- Add to Cart Form -->
                <form method="POST" class="space-y-4">
                    <div class="form-control w-32">
                        <label class="label text-xs font-bold">Select Quantity</label>
                        <div class="join border border-base-300 rounded-lg">
                            <input type="number" name="quantity" value="1" min="1" max="50" class="input input-sm join-item w-full text-center font-bold focus:outline-none" />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" name="add_to_cart" class="btn btn-primary flex-1 shadow-lg gap-2">
                            <i class="fa-solid fa-cart-plus"></i> Add to Cart
                        </button>
                        <a href="restaurant.php?id=<?= $dish['restaurant_id']; ?>" class="btn btn-ghost">
                            View Menu
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Customer Reviews List -->
    <div class="space-y-4 pt-6 border-t border-base-300">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold font-heading flex items-center gap-2">
                <i class="fa-solid fa-comments text-secondary"></i> Customer Reviews (<?= $reviews->num_rows; ?>)
            </h3>
            <button onclick="dish_review_modal.showModal()" class="btn btn-primary btn-sm gap-2 shadow">
                <i class="fa-solid fa-star"></i> Leave a Review Modal
            </button>
        </div>
        
        <?php if ($reviews->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php while ($rev = $reviews->fetch_assoc()): ?>
                    <div class="card bg-base-100 shadow-xs border border-base-200 p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-8">
                                        <span class="text-xs"><?= strtoupper(substr($rev['user_name'], 0, 1)); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm"><?= htmlspecialchars($rev['user_name']); ?></h4>
                                    <span class="text-xs text-base-content/50"><?= date('d M Y', strtotime($rev['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="flex text-warning text-xs gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= $rev['rating'] ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-xs text-base-content/80 mt-3"><?= htmlspecialchars($rev['comment']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-ghost bg-base-100 border border-base-200">
                <span class="text-xs text-base-content/70">No reviews yet for this dish. Be the first to leave a review!</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
