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
    $stmt_f = $conn->prepare("SELECT * FROM foods WHERE id = ? AND is_active = 1");
    $stmt_f->bind_param("i", $food_id);
    $stmt_f->execute();
    $f_item = $stmt_f->get_result()->fetch_assoc();
    
    if ($f_item) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $food_id) {
                $item['quantity'] += 1;
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
                'quantity' => 1
            ];
        }
        $msg = "Added '" . htmlspecialchars($f_item['name']) . "' to your cart!";
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
                <div class="text-xs">
                    <span class="block font-semibold"><i class="fa-regular fa-clock text-info mr-1"></i> Hours:</span>
                    <span class="text-base-content/70"><?= htmlspecialchars($restaurant['opening_hours'] ?? '10:00 AM - 10:00 PM'); ?></span>
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
                                <a href="dish.php?id=<?= $d['id']; ?>" class="btn btn-ghost btn-xs">Details</a>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="food_id" value="<?= $d['id']; ?>">
                                    <button type="submit" name="quick_add_to_cart" class="btn btn-primary btn-sm rounded-lg shadow-sm gap-1">
                                        <i class="fa-solid fa-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>No dishes are currently active for this restaurant.</span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Reviews & Ratings Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-6 border-t border-base-300">
        <!-- Review Submission Form -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-md border border-base-200 p-6">
                <h3 class="text-lg font-bold font-heading mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-primary"></i> Write a Review
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
                            <label class="label text-xs font-semibold">Your Feedback / Comment</label>
                            <textarea name="comment" rows="3" required placeholder="Tell us about your food & dining experience..." class="textarea textarea-bordered w-full text-sm"></textarea>
                        </div>
                        <button type="submit" name="submit_restaurant_review" class="btn btn-primary btn-block shadow-md">
                            Submit Review
                        </button>
                    </form>
                <?php else: ?>
                    <div class="bg-base-200 p-4 rounded-xl text-center space-y-3">
                        <p class="text-xs text-base-content/70">Please login to share your rating and review for this restaurant.</p>
                        <a href="login.php?redirect=restaurant.php?id=<?= $id; ?>" class="btn btn-primary btn-sm btn-block">Login to Review</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Customer Reviews List -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-xl font-bold font-heading flex items-center gap-2">
                <i class="fa-solid fa-comments text-secondary"></i> Customer Reviews (<?= $reviews->num_rows; ?>)
            </h3>
            
            <?php if ($reviews->num_rows > 0): ?>
                <div class="space-y-3">
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
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
