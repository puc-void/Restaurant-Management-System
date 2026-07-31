<?php
$id = intval($_GET['id'] ?? 0);
require_once 'includes/config.php';

$stmt = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();

if (!$restaurant) {
    header("Location: index.php");
    exit;
}

$pageTitle = htmlspecialchars($restaurant['name']) . " - Restaurant Details";
require_once 'includes/header.php';

// Handle quick add to cart
if (isset($_POST['quick_add_to_cart'])) {
    $food_id = intval($_POST['food_id']);
    $qty = max(1, intval($_POST['quantity'] ?? 1));
    $stmt_f = $conn->prepare("SELECT * FROM foods WHERE id = ? AND is_active = 1");
    $stmt_f->bind_param("i", $food_id);
    $stmt_f->execute();
    $f_item = $stmt_f->get_result()->fetch_assoc();
    
    if ($f_item) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $food_id) {
                $item['quantity'] += $qty;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $f_item['id'],
                'name' => $f_item['name'],
                'price' => $f_item['price'],
                'image' => $f_item['image_url'],
                'quantity' => $qty
            ];
        }
        $msg = "Added " . $qty . "x '" . htmlspecialchars($f_item['name']) . "' to your cart!";
    }
}

// Handle submitting review for restaurant
if (isset($_POST['submit_restaurant_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=restaurant.php?id=" . $id);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $rating = max(1, min(5, intval($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');

    $stmt_rev = $conn->prepare("INSERT INTO reviews (user_id, restaurant_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt_rev->bind_param("iiis", $user_id, $id, $rating, $comment);
    $stmt_rev->execute();
    $review_msg = "Thank you for your rating & review!";
}

// Fetch dishes
$dishes = $conn->query("SELECT * FROM foods WHERE restaurant_id = $id AND is_active = 1 ORDER BY id DESC");

// Fetch reviews
$stmt_r_rev = $conn->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.restaurant_id = ? 
    ORDER BY r.created_at DESC
");
$stmt_r_rev->bind_param("i", $id);
$stmt_r_rev->execute();
$reviews = $stmt_r_rev->get_result();

$restaurant_rating = get_restaurant_rating($conn, $id);
?>

<!-- Hero Banner -->
<div class="relative bg-base-300 min-h-[300px] flex items-end">
    <img src="<?= !empty($restaurant['image_url']) ? htmlspecialchars($restaurant['image_url']) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80'; ?>" 
         alt="<?= htmlspecialchars($restaurant['name']); ?>" 
         class="absolute inset-0 w-full h-full object-cover brightness-50" />
    
    <div class="relative z-10 container mx-auto px-4 py-8 text-white">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <div class="badge badge-primary font-semibold mb-2"><?= htmlspecialchars($restaurant['category'] ?? 'Restaurant'); ?></div>
                <h1 class="text-3xl sm:text-5xl font-extrabold font-heading drop-shadow-md">
                    <?= htmlspecialchars($restaurant['name']); ?>
                </h1>
                <p class="text-white/80 text-sm mt-2 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-error"></i> <?= htmlspecialchars($restaurant['address']); ?>
                    <?php if (!empty($restaurant['phone'])): ?>
                        <span>•</span> <i class="fa-solid fa-phone text-success"></i> <?= htmlspecialchars($restaurant['phone']); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="bg-base-100/90 backdrop-blur text-base-content p-4 rounded-2xl shadow-xl flex items-center gap-4">
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-warning flex items-center justify-center gap-1">
                        <i class="fa-solid fa-star"></i> <?= $restaurant_rating['avg']; ?>
                    </div>
                    <span class="text-xs text-base-content/60"><?= $restaurant_rating['count']; ?> Customer Reviews</span>
                </div>
                <div class="divider divider-horizontal my-0"></div>
                <div class="flex flex-col gap-2">
                    <span class="text-xs text-base-content/70"><i class="fa-regular fa-clock text-info mr-1"></i> <?= htmlspecialchars($restaurant['opening_hours'] ?? '10:00 AM - 10:00 PM'); ?></span>
                    <button onclick="write_review_modal.showModal()" class="btn btn-primary btn-sm gap-2 shadow">
                        <i class="fa-solid fa-star"></i> Write a Review
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-10 space-y-12">

    <!-- Alert Messages -->
    <?php if (isset($msg)): ?>
        <div class="alert alert-success shadow-md">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span><?= $msg; ?></span>
            <a href="cart.php" class="btn btn-sm btn-ghost">View Cart</a>
        </div>
    <?php endif; ?>

    <?php if (isset($review_msg)): ?>
        <div class="alert alert-success shadow-md">
            <i class="fa-solid fa-star text-lg text-warning"></i>
            <span><?= $review_msg; ?></span>
        </div>
    <?php endif; ?>

    <!-- Write Review DaisyUI Modal -->
    <dialog id="write_review_modal" class="modal">
        <div class="modal-box max-w-md bg-base-100 p-6">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3">✕</button>
            </form>
            <h3 class="text-xl font-bold font-heading mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-primary"></i> Write a Review for <?= htmlspecialchars($restaurant['name']); ?>
            </h3>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="label text-xs font-semibold">Star Rating</label>
                        <div class="rating rating-lg gap-1">
                            <input type="radio" name="rating" value="1" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="2" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="3" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="4" class="mask mask-star-2 bg-warning" />
                            <input type="radio" name="rating" value="5" class="mask mask-star-2 bg-warning" checked />
                        </div>
                    </div>
                    <div>
                        <label class="label text-xs font-semibold">Your Experience / Feedback</label>
                        <textarea name="comment" rows="4" required placeholder="Tell us about the taste, service & ambiance..." class="textarea textarea-bordered w-full text-sm"></textarea>
                    </div>
                    <button type="submit" name="submit_restaurant_review" class="btn btn-primary btn-block shadow-md">
                        Submit Review Now
                    </button>
                </form>
            <?php else: ?>
                <div class="bg-base-200 p-6 rounded-xl text-center space-y-3">
                    <p class="text-xs text-base-content/70">Please login to share your rating and review.</p>
                    <a href="login.php?redirect=restaurant.php?id=<?= $id; ?>" class="btn btn-primary btn-sm btn-block">Login to Review</a>
                </div>
            <?php endif; ?>
        </div>
        <form method="dialog" class="modal-backdrop bg-neutral/60">
            <button>close</button>
        </form>
    </dialog>

    <!-- Menu Section -->
    <section>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold font-heading">Menu & Available Dishes</h2>
                <p class="text-xs text-base-content/70">Select dishes to add to your order</p>
            </div>
            <a href="cart.php" class="btn btn-primary btn-sm gap-2">
                <i class="fa-solid fa-cart-shopping"></i> View Cart (<?= get_cart_count(); ?>)
            </a>
        </div>

        <?php if ($dishes->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($d = $dishes->fetch_assoc()): ?>
                    <?php $food_rating = get_food_rating($conn, $d['id']); ?>
                    <div class="card bg-base-100 shadow-md hover:shadow-xl border border-base-200 transition-all flex flex-col justify-between overflow-hidden group">
                        <div>
                            <figure class="relative h-44 overflow-hidden bg-base-300">
                                <img src="<?= !empty($d['image_url']) ? htmlspecialchars($d['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'; ?>"
                                     alt="<?= htmlspecialchars($d['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                <div class="absolute top-2 right-2 bg-base-100/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow">
                                    <i class="fa-solid fa-star text-warning"></i> <?= $food_rating['avg']; ?>
                                </div>
                            </figure>
                            <div class="card-body p-4">
                                <h3 class="card-title text-base font-bold font-heading">
                                    <a href="dish.php?id=<?= $d['id']; ?>" class="hover:text-primary transition">
                                        <?= htmlspecialchars($d['name']); ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-base-content/70 line-clamp-2">
                                    <?= htmlspecialchars($d['description']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="p-4 pt-0 flex items-center justify-between border-t border-base-200 mt-2">
                            <span class="text-lg font-bold text-primary font-heading">$<?= number_format($d['price'], 2); ?></span>
                            <div class="flex gap-2">
                                <button type="button" onclick="r_dish_modal_<?= $d['id']; ?>.showModal()" class="btn btn-ghost btn-circle btn-sm" title="Quick View Modal">
                                    <i class="fa-solid fa-expand text-base-content/70"></i>
                                </button>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="food_id" value="<?= $d['id']; ?>">
                                    <button type="submit" name="quick_add_to_cart" class="btn btn-primary btn-sm rounded-lg shadow-sm gap-1">
                                        <i class="fa-solid fa-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Quick View Dish Modal -->
                    <dialog id="r_dish_modal_<?= $d['id']; ?>" class="modal">
                        <div class="modal-box max-w-md p-0 overflow-hidden bg-base-100">
                            <form method="dialog">
                                <button class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3 z-20 text-white bg-black/50 hover:bg-black/80 border-none">✕</button>
                            </form>
                            <div class="relative h-52 bg-base-300">
                                <img src="<?= !empty($d['image_url']) ? htmlspecialchars($d['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'; ?>" 
                                     alt="<?= htmlspecialchars($d['name']); ?>" 
                                     class="w-full h-full object-cover" />
                            </div>
                            <div class="p-6 space-y-3">
                                <h3 class="text-2xl font-bold font-heading"><?= htmlspecialchars($d['name']); ?></h3>
                                <p class="text-xs text-base-content/80"><?= htmlspecialchars($d['description']); ?></p>
                                <form method="POST" class="pt-3 border-t border-base-200 space-y-4">
                                    <input type="hidden" name="food_id" value="<?= $d['id']; ?>">
                                    <div class="flex items-center justify-between">
                                        <span class="text-2xl font-extrabold text-primary font-heading">$<?= number_format($d['price'], 2); ?></span>
                                        <input type="number" name="quantity" value="1" min="1" max="50" class="input input-sm input-bordered w-24 text-center font-bold" />
                                    </div>
                                    <button type="submit" name="quick_add_to_cart" class="btn btn-primary btn-block shadow-lg gap-2">
                                        <i class="fa-solid fa-cart-plus"></i> Add to Cart Now
                                    </button>
                                </form>
                            </div>
                        </div>
                        <form method="dialog" class="modal-backdrop bg-neutral/60"><button>close</button></form>
                    </dialog>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>No dishes are currently active for this restaurant.</span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Customer Reviews List -->
    <section class="space-y-4 pt-6 border-t border-base-300">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-bold font-heading flex items-center gap-2">
                <i class="fa-solid fa-comments text-secondary"></i> Customer Reviews (<?= $reviews->num_rows; ?>)
            </h3>
            <button onclick="write_review_modal.showModal()" class="btn btn-outline btn-primary btn-sm gap-2">
                <i class="fa-solid fa-star"></i> Write Review Modal
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
                <span class="text-xs text-base-content/70">No reviews submitted yet. Be the first to review <?= htmlspecialchars($restaurant['name']); ?>!</span>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
