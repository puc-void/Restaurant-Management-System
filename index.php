<?php
$pageTitle = "GourmetHub - Discover & Order Best Food";
require_once 'includes/header.php';

// Handle quick add to cart from index page
if (isset($_POST['quick_add_to_cart'])) {
    $food_id = intval($_POST['food_id']);
    $stmt_f = $conn->prepare("SELECT f.*, r.name as restaurant_name FROM foods f JOIN restaurants r ON f.restaurant_id = r.id WHERE f.id = ? AND f.is_active = 1");
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
        $quick_added_msg = "Added '" . htmlspecialchars($f_item['name']) . "' to cart!";
    }
}

// Category filter
$selected_category = $_GET['category'] ?? 'All';

// Fetch Restaurants
if ($selected_category !== 'All') {
    $stmt_r = $conn->prepare("SELECT * FROM restaurants WHERE category = ? ORDER BY id DESC");
    $stmt_r->bind_param("s", $selected_category);
    $stmt_r->execute();
    $restaurants = $stmt_r->get_result();

    $stmt_d = $conn->prepare("
        SELECT f.*, r.name AS restaurant_name 
        FROM foods f 
        JOIN restaurants r ON f.restaurant_id = r.id
        WHERE f.is_active = 1 AND (f.category = ? OR r.category = ?)
        ORDER BY f.id DESC
    ");
    $stmt_d->bind_param("ss", $selected_category, $selected_category);
    $stmt_d->execute();
    $dishes = $stmt_d->get_result();
} else {
    $restaurants = $conn->query("SELECT * FROM restaurants ORDER BY id DESC");
    $dishes = $conn->query("
        SELECT f.*, r.name AS restaurant_name 
        FROM foods f 
        JOIN restaurants r ON f.restaurant_id = r.id
        WHERE f.is_active = 1
        ORDER BY f.id DESC
    ");
}

$categories = ['All', 'Fast Food', 'Italian', 'Indian', 'Asian', 'Pizza', 'Desserts', 'Beverages'];
?>

<!-- Hero Banner -->
<div class="hero min-h-[440px] relative bg-cover bg-center overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1600&q=80');">
    <div class="hero-overlay bg-neutral/80 backdrop-blur-xs"></div>
    <div class="hero-content text-center text-neutral-content py-12 px-4">
        <div class="max-w-2xl">
            <span class="badge badge-primary badge-lg mb-4 font-semibold shadow-md"><i class="fa-solid fa-fire mr-2"></i> Craving Gourmet Food?</span>
            <h1 class="mb-5 text-4xl sm:text-5xl font-extrabold font-heading text-white leading-tight">
                Discover Top Restaurants & Delicious Dishes
            </h1>
            <p class="mb-8 text-neutral-content/80 text-lg">
                Freshly prepared meals from top local restaurants delivered straight to your door with real-time tracking.
            </p>
            
            <!-- Quick Search Bar -->
            <form action="search.php" method="GET" class="join w-full max-w-xl shadow-2xl">
                <input type="text" name="q" placeholder="Search for burgers, pizza, restaurants..." class="input input-lg input-bordered join-item w-full text-base-content focus:outline-none" />
                <button type="submit" class="btn btn-lg btn-primary join-item gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> <span class="hidden sm:inline">Search</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Alert for Quick Add to Cart -->
<?php if (isset($quick_added_msg)): ?>
    <div class="container mx-auto px-4 mt-6">
        <div class="alert alert-success shadow-lg">
            <i class="fa-solid fa-circle-check text-xl"></i>
            <span><?= $quick_added_msg; ?></span>
            <div>
                <a href="cart.php" class="btn btn-sm btn-ghost">View Cart</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Category Filter Pills Bar -->
<div class="bg-base-100 border-b border-base-300 py-6 sticky top-[65px] z-40 shadow-xs">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between gap-4 overflow-x-auto pb-2 scrollbar-none">
            <div class="font-heading font-bold text-sm uppercase text-base-content/60 flex items-center gap-2 flex-shrink-0">
                <i class="fa-solid fa-filter text-primary"></i> Filter:
            </div>
            <div class="flex gap-2 flex-wrap">
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?category=<?= urlencode($cat); ?>" 
                       class="btn btn-sm rounded-full <?= $selected_category === $cat ? 'btn-primary' : 'btn-ghost bg-base-200'; ?> shadow-xs font-medium">
                        <?= htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="container mx-auto px-4 py-10 space-y-16">

    <!-- Top Restaurants Section -->
    <section>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-2">
            <div>
                <div class="badge badge-secondary mb-2 font-medium">Top Rated</div>
                <h2 class="text-3xl font-bold font-heading">Featured Restaurants</h2>
                <p class="text-base-content/70 text-sm">Explore finest dining spots with authentic cuisines</p>
            </div>
            <a href="search.php" class="btn btn-ghost btn-sm text-primary gap-1">Browse All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <?php if ($restaurants->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($r = $restaurants->fetch_assoc()): ?>
                    <?php $rating_info = get_restaurant_rating($conn, $r['id']); ?>
                    <a href="restaurant.php?id=<?= $r['id'] ?>" class="group">
                        <div class="card bg-base-100 shadow-md hover:shadow-2xl transition-all duration-300 border border-base-200 h-full overflow-hidden">
                            <figure class="relative h-48 overflow-hidden bg-base-300">
                                <img src="<?= !empty($r['image_url']) ? htmlspecialchars($r['image_url']) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80'; ?>"
                                     alt="<?= htmlspecialchars($r['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div class="absolute top-3 left-3">
                                    <span class="badge badge-accent shadow-md font-semibold"><?= htmlspecialchars($r['category'] ?? 'Restaurant'); ?></span>
                                </div>
                                <div class="absolute top-3 right-3 bg-base-100/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow">
                                    <i class="fa-solid fa-star text-warning"></i>
                                    <span><?= $rating_info['avg']; ?></span>
                                    <span class="text-base-content/60">('<?= $rating_info['count']; ?>)</span>
                                </div>
                            </figure>
                            <div class="card-body p-5">
                                <h3 class="card-title text-lg font-bold font-heading group-hover:text-primary transition-colors">
                                    <?= htmlspecialchars($r['name']); ?>
                                </h3>
                                <p class="text-xs text-base-content/70 flex items-center gap-1 truncate">
                                    <i class="fa-solid fa-location-dot text-error"></i> <?= htmlspecialchars($r['address']); ?>
                                </p>
                                <div class="card-actions justify-between items-center mt-3 pt-3 border-t border-base-200 text-xs">
                                    <span class="text-base-content/60 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-info"></i> <?= htmlspecialchars($r['opening_hours'] ?? '10:00 AM - 10:00 PM'); ?>
                                    </span>
                                    <span class="text-primary font-semibold group-hover:translate-x-1 transition-transform">
                                        Menu <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info shadow-sm">
                <i class="fa-solid fa-info-circle"></i>
                <span>No restaurants found for category "<strong><?= htmlspecialchars($selected_category); ?></strong>".</span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Banner Feature Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card bg-primary text-primary-content p-6 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-bold">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <h4 class="font-bold font-heading text-lg">Super Fast Delivery</h4>
                    <p class="text-xs opacity-90">Hot & fresh food delivered to your door in 30 mins.</p>
                </div>
            </div>
        </div>
        <div class="card bg-secondary text-secondary-content p-6 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-bold">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <h4 class="font-bold font-heading text-lg">Gourmet Quality</h4>
                    <p class="text-xs opacity-90">Prepared by master chefs using premium ingredients.</p>
                </div>
            </div>
        </div>
        <div class="card bg-accent text-accent-content p-6 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-bold">
                    <i class="fa-solid fa-shield-heart"></i>
                </div>
                <div>
                    <h4 class="font-bold font-heading text-lg">Secure & Easy Pay</h4>
                    <p class="text-xs opacity-90">Support Cash, bKash, Nagad, Rocket & Cards.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Dishes Section -->
    <section>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-2">
            <div>
                <div class="badge badge-primary mb-2 font-medium">Chef's Special</div>
                <h2 class="text-3xl font-bold font-heading">Popular & Trending Dishes</h2>
                <p class="text-base-content/70 text-sm">Delicious customer favorites prepared to perfection</p>
            </div>
            <a href="search.php" class="btn btn-ghost btn-sm text-primary gap-1">View Full Menu <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <?php if ($dishes->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($d = $dishes->fetch_assoc()): ?>
                    <?php $food_rating = get_food_rating($conn, $d['id']); ?>
                    <div class="card bg-base-100 shadow-md hover:shadow-2xl transition-all duration-300 border border-base-200 h-full flex flex-col justify-between overflow-hidden group">
                        <div>
                            <figure class="relative h-48 overflow-hidden bg-base-300">
                                <img src="<?= !empty($d['image_url']) ? htmlspecialchars($d['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'; ?>" 
                                     alt="<?= htmlspecialchars($d['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div class="absolute top-3 left-3">
                                    <span class="badge badge-neutral bg-base-100/90 text-base-content border-none shadow font-semibold text-xs">
                                        <?= htmlspecialchars($d['category'] ?? 'Main'); ?>
                                    </span>
                                </div>
                                <div class="absolute top-3 right-3 bg-base-100/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow">
                                    <i class="fa-solid fa-star text-warning"></i>
                                    <span><?= $food_rating['avg']; ?></span>
                                    <span class="text-base-content/60">('<?= $food_rating['count']; ?>)</span>
                                </div>
                            </figure>
                            <div class="card-body p-5">
                                <span class="text-xs text-secondary font-semibold uppercase tracking-wider">
                                    <?= htmlspecialchars($d['restaurant_name']); ?>
                                </span>
                                <h3 class="card-title text-base font-bold font-heading line-clamp-1">
                                    <a href="dish.php?id=<?= $d['id']; ?>" class="hover:text-primary transition">
                                        <?= htmlspecialchars($d['name']); ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-base-content/70 line-clamp-2 mt-1">
                                    <?= htmlspecialchars($d['description']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="px-5 pb-5 pt-2 flex items-center justify-between border-t border-base-200">
                            <div>
                                <span class="text-xs text-base-content/60 block">Price</span>
                                <span class="text-lg font-extrabold text-primary font-heading">$<?= number_format($d['price'], 2); ?></span>
                            </div>
                            <div class="flex gap-2">
                                <a href="dish.php?id=<?= $d['id']; ?>" class="btn btn-ghost btn-circle btn-sm" title="View Details">
                                    <i class="fa-solid fa-eye text-base-content/70"></i>
                                </a>
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
            <div class="alert alert-info shadow-sm">
                <i class="fa-solid fa-info-circle"></i>
                <span>No dishes found for category "<strong><?= htmlspecialchars($selected_category); ?></strong>".</span>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
