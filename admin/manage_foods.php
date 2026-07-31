<?php
$pageTitle = "Manage Dishes";
require_once __DIR__ . '/header.php';

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM foods WHERE id = $delete_id");
    header("Location: manage_foods.php?deleted=1");
    exit;
}

// Handle AJAX search + filter
if (isset($_GET['ajax'])) {
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $restaurant_id = intval($_GET['restaurant_id'] ?? 0);

    $query = "
        SELECT f.*, r.name AS restaurant_name
        FROM foods f
        LEFT JOIN restaurants r ON f.restaurant_id = r.id
        WHERE (f.name LIKE '%$search%' OR r.name LIKE '%$search%')
    ";
    if ($restaurant_id > 0) {
        $query .= " AND f.restaurant_id = $restaurant_id";
    }
    $query .= " ORDER BY f.created_at DESC";

    $foods = $conn->query($query);
    if ($foods->num_rows === 0) {
        echo '<tr><td colspan="6" class="text-center text-base-content/60 py-4">No dishes found.</td></tr>';
    } else {
        while ($f = $foods->fetch_assoc()) {
            echo '<tr>
                    <td class="font-bold">#'.htmlspecialchars($f['id']).'</td>
                    <td class="font-bold text-sm">'.htmlspecialchars($f['name']).'</td>
                    <td>'.htmlspecialchars($f['restaurant_name']).'</td>
                    <td class="font-extrabold text-primary">$'.number_format($f['price'],2).'</td>
                    <td><span class="badge '.($f['is_active'] ? 'badge-success' : 'badge-ghost').' badge-sm font-bold">'.($f['is_active'] ? 'Active' : 'Inactive').'</span></td>
                    <td class="text-right flex items-center justify-end gap-2">
                        <a href="edit_food.php?id='.$f['id'].'" class="btn btn-ghost btn-xs text-primary"><i class="fa-solid fa-pen"></i> Edit</a>
                        <a href="manage_foods.php?delete_id='.$f['id'].'" onclick="return confirm(\'Delete this dish?\');" class="btn btn-ghost btn-xs text-error"><i class="fa-solid fa-trash"></i> Delete</a>
                    </td>
                </tr>';
        }
    }
    exit;
}

$restaurants = $conn->query("SELECT id, name FROM restaurants ORDER BY name ASC");
$foods = $conn->query("
    SELECT f.*, r.name AS restaurant_name
    FROM foods f
    LEFT JOIN restaurants r ON f.restaurant_id = r.id
    ORDER BY f.created_at DESC
");
?>

<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-burger text-primary"></i> Manage Dishes & Menu
            </h1>
            <p class="text-xs text-base-content/70">Add, edit, or toggle availability of menu items</p>
        </div>
        <a href="add_food.php" class="btn btn-primary btn-sm gap-2 shadow">
            <i class="fa-solid fa-plus"></i> Add New Dish
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning shadow-md text-xs">
            <i class="fa-solid fa-trash-can"></i> Dish deleted successfully.
        </div>
    <?php endif; ?>

    <!-- Search & Restaurant Filter Bar -->
    <div class="card bg-base-100 shadow-md border border-base-200 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" id="search" placeholder="Search by dish name or restaurant..." class="input input-bordered w-full text-sm pl-10" />
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-base-content/40"></i>
            </div>

            <select id="restaurant-filter" class="select select-bordered text-sm">
                <option value="0">All Restaurants</option>
                <?php while ($r = $restaurants->fetch_assoc()): ?>
                    <option value="<?= $r['id']; ?>"><?= htmlspecialchars($r['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <!-- Foods Table -->
    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-xs">
                <thead>
                    <tr class="font-bold uppercase bg-base-200">
                        <th>ID</th>
                        <th>Dish Name</th>
                        <th>Restaurant</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="food-list">
                    <?php while ($f = $foods->fetch_assoc()): ?>
                        <tr class="hover">
                            <td class="font-bold">#<?= $f['id']; ?></td>
                            <td class="font-bold text-sm"><?= htmlspecialchars($f['name']); ?></td>
                            <td><?= htmlspecialchars($f['restaurant_name'] ?? 'Unassigned'); ?></td>
                            <td class="font-extrabold text-primary">$<?= number_format($f['price'], 2); ?></td>
                            <td>
                                <span class="badge <?= $f['is_active'] ? 'badge-success' : 'badge-ghost'; ?> badge-sm font-bold">
                                    <?= $f['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="text-right flex items-center justify-end gap-2">
                                <a href="edit_food.php?id=<?= $f['id']; ?>" class="btn btn-ghost btn-xs text-primary" title="Edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="manage_foods.php?delete_id=<?= $f['id']; ?>" onclick="return confirm('Delete this dish?');" class="btn btn-ghost btn-xs text-error" title="Delete">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($foods->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60 py-8">
                                No dishes currently found. Click "Add New Dish" to add one!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    function filterFoods() {
        const search = $('#search').val();
        const restaurant_id = $('#restaurant-filter').val();
        $.get('manage_foods.php', { ajax: 1, search: search, restaurant_id: restaurant_id }, function(data) {
            $('#food-list').html(data);
        });
    }
    $('#search').on('keyup', filterFoods);
    $('#restaurant-filter').on('change', filterFoods);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
