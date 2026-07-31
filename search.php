<?php
$pageTitle = "Search Menu & Restaurants - GourmetHub";
require_once 'includes/config.php';

$query = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'All');

$restaurants_res = [];
$dishes_res = [];

if ($query !== '' || $category !== 'All') {
    // Search Restaurants
    $r_sql = "SELECT * FROM restaurants WHERE 1=1";
    $r_params = [];
    $r_types = "";

    if ($query !== '') {
        $r_sql .= " AND (name LIKE ? OR address LIKE ? OR category LIKE ?)";
        $searchTerm = "%$query%";
        $r_params[] = $searchTerm;
        $r_params[] = $searchTerm;
        $r_params[] = $searchTerm;
        $r_types .= "sss";
    }

    if ($category !== 'All') {
        $r_sql .= " AND category = ?";
        $r_params[] = $category;
        $r_types .= "s";
    }

    $stmt_r = $conn->prepare($r_sql);
    if (!empty($r_params)) {
        $stmt_r->bind_param($r_types, ...$r_params);
    }
    $stmt_r->execute();
    $restaurants_res = $stmt_r->get_result();

    // Search Foods / Dishes
    $d_sql = "
        SELECT f.*, r.name AS restaurant_name 
        FROM foods f 
        JOIN restaurants r ON f.restaurant_id = r.id 
        WHERE f.is_active = 1
    ";
    $d_params = [];
    $d_types = "";

    if ($query !== '') {
        $d_sql .= " AND (f.name LIKE ? OR f.description LIKE ? OR r.name LIKE ?)";
        $searchTerm = "%$query%";
        $d_params[] = $searchTerm;
        $d_params[] = $searchTerm;
        $d_params[] = $searchTerm;
        $d_types .= "sss";
    }

    if ($category !== 'All') {
        $d_sql .= " AND (f.category = ? OR r.category = ?)";
        $d_params[] = $category;
        $d_params[] = $category;
        $d_types .= "ss";
    }

    $stmt_d = $conn->prepare($d_sql);
    if (!empty($d_params)) {
        $stmt_d->bind_param($d_types, ...$d_params);
    }
    $stmt_d->execute();
    $dishes_res = $stmt_d->get_result();
} else {
    $restaurants_res = $conn->query("SELECT * FROM restaurants ORDER BY id DESC");
    $dishes_res = $conn->query("
        SELECT f.*, r.name AS restaurant_name 
        FROM foods f 
        JOIN restaurants r ON f.restaurant_id = r.id 
        WHERE f.is_active = 1 
        ORDER BY f.id DESC
    ");
}

$categories = ['All', 'Fast Food', 'Italian', 'Indian', 'Asian', 'Pizza', 'Desserts', 'Beverages'];
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-10 max-w-6xl space-y-10">

    <!-- Search Bar & Filters -->
    <div class="card bg-base-100 shadow-xl border border-base-200 p-6 md:p-8 space-y-6">
        <h1 class="text-3xl font-extrabold font-heading text-center flex items-center justify-center gap-3">
            <i class="fa-solid fa-magnifying-glass text-primary"></i> Explore Menu & Restaurants
        </h1>

        <form method="GET" action="search.php" class="flex flex-col sm:flex-row gap-3 max-w-3xl mx-auto w-full">
            <input type="text" name="q" value="<?= htmlspecialchars($query); ?>" placeholder="Search burgers, pasta, curry, restaurants..." 
                   class="input input-lg input-bordered w-full text-base" />
            
            <select name="category" class="select select-lg select-bordered">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat); ?>" <?= $category === $cat ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-lg btn-primary shadow-md gap-2">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </form>
    </div>

    <!-- Restaurants Search Results -->
    <section>
        <h2 class="text-2xl font-bold font-heading mb-4 flex items-center gap-2">
            <i class="fa-solid fa-store text-primary"></i> Restaurants (<?= is_object($restaurants_res) ? $restaurants_res->num_rows : 0; ?>)
        </h2>

        <?php if (is_object($restaurants_res) && $restaurants_res->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($r = $restaurants_res->fetch_assoc()): ?>
                    <?php $rating_info = get_restaurant_rating($conn, $r['id']); ?>
                    <a href="restaurant.php?id=<?= $r['id'] ?>" class="group">
                        <div class="card bg-base-100 shadow-md hover:shadow-xl transition-all border border-base-200 h-full overflow-hidden">
                            <figure class="relative h-40 overflow-hidden bg-base-300">
                                <img src="<?= !empty($r['image_url']) ? htmlspecialchars($r['image_url']) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80'; ?>" 
                                     alt="<?= htmlspecialchars($r['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                <div class="absolute top-2 right-2 bg-base-100/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold shadow">
                                    <i class="fa-solid fa-star text-warning"></i> <?= $rating_info['avg']; ?>
                                </div>
                            </figure>
                            <div class="card-body p-4">
                                <h3 class="card-title text-base font-bold font-heading group-hover:text-primary transition-colors">
                                    <?= htmlspecialchars($r['name']); ?>
                                </h3>
                                <p class="text-xs text-base-content/70 truncate"><i class="fa-solid fa-location-dot text-error mr-1"></i> <?= htmlspecialchars($r['address']); ?></p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-ghost bg-base-100 border border-base-200">
                <span class="text-xs text-base-content/70">No matching restaurants found.</span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Dishes Search Results -->
    <section>
        <h2 class="text-2xl font-bold font-heading mb-4 flex items-center gap-2">
            <i class="fa-solid fa-utensils text-secondary"></i> Dishes & Food Items (<?= is_object($dishes_res) ? $dishes_res->num_rows : 0; ?>)
        </h2>

        <?php if (is_object($dishes_res) && $dishes_res->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($d = $dishes_res->fetch_assoc()): ?>
                    <?php $food_rating = get_food_rating($conn, $d['id']); ?>
                    <div class="card bg-base-100 shadow-md border border-base-200 flex flex-col justify-between overflow-hidden group">
                        <div>
                            <figure class="relative h-44 overflow-hidden bg-base-300">
                                <img src="<?= !empty($d['image_url']) ? htmlspecialchars($d['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'; ?>" 
                                     alt="<?= htmlspecialchars($d['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                                <div class="absolute top-2 right-2 bg-base-100/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold shadow">
                                    <i class="fa-solid fa-star text-warning"></i> <?= $food_rating['avg']; ?>
                                </div>
                            </figure>
                            <div class="card-body p-4">
                                <span class="text-xs text-secondary font-bold uppercase"><?= htmlspecialchars($d['restaurant_name']); ?></span>
                                <h3 class="card-title text-base font-bold font-heading">
                                    <a href="dish.php?id=<?= $d['id']; ?>" class="hover:text-primary transition"><?= htmlspecialchars($d['name']); ?></a>
                                </h3>
                                <p class="text-xs text-base-content/70 line-clamp-2"><?= htmlspecialchars($d['description']); ?></p>
                            </div>
                        </div>

                        <div class="p-4 pt-0 flex items-center justify-between border-t border-base-200 mt-2">
                            <span class="text-lg font-bold text-primary font-heading">$<?= number_format($d['price'], 2); ?></span>
                            <a href="dish.php?id=<?= $d['id']; ?>" class="btn btn-primary btn-sm rounded-lg shadow-sm gap-1">
                                Details <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-ghost bg-base-100 border border-base-200">
                <span class="text-xs text-base-content/70">No matching dishes found.</span>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
