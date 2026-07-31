<?php
$pageTitle = "Manage Restaurants";
require_once __DIR__ . '/header.php';

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM restaurants WHERE id = $delete_id");
    header("Location: manage_restaurants.php?deleted=1");
    exit;
}

if (isset($_GET['ajax'])) {
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $query = "SELECT * FROM restaurants WHERE name LIKE '%$search%' OR address LIKE '%$search%' OR category LIKE '%$search%' ORDER BY created_at DESC";
    $restaurants = $conn->query($query);

    if ($restaurants->num_rows === 0) {
        echo '<tr><td colspan="6" class="text-center text-base-content/60 py-4">No restaurants found.</td></tr>';
    } else {
        while ($r = $restaurants->fetch_assoc()) {
            echo '<tr>
                    <td class="font-bold">#'.htmlspecialchars($r['id']).'</td>
                    <td class="font-bold text-sm">'.htmlspecialchars($r['name']).'</td>
                    <td><span class="badge badge-accent badge-sm font-semibold">'.htmlspecialchars($r['category'] ?? 'General').'</span></td>
                    <td class="text-base-content/70">'.htmlspecialchars($r['address']).'</td>
                    <td class="text-base-content/70">'.date('d M Y', strtotime($r['created_at'])).'</td>
                    <td class="text-right flex items-center justify-end gap-2">
                        <a href="edit_restaurant.php?id='.$r['id'].'" class="btn btn-ghost btn-xs text-primary"><i class="fa-solid fa-pen"></i> Edit</a>
                        <a href="manage_restaurants.php?delete_id='.$r['id'].'" onclick="return confirm(\'Delete this restaurant?\');" class="btn btn-ghost btn-xs text-error"><i class="fa-solid fa-trash"></i> Delete</a>
                    </td>
                </tr>';
        }
    }
    exit;
}

$restaurants = $conn->query("SELECT * FROM restaurants ORDER BY created_at DESC");
?>

<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-store text-primary"></i> Manage Restaurants
            </h1>
            <p class="text-xs text-base-content/70">View, add, edit, or remove partner restaurants</p>
        </div>
        <a href="add_restaurant.php" class="btn btn-primary btn-sm gap-2 shadow">
            <i class="fa-solid fa-plus"></i> Add New Restaurant
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning shadow-md text-xs">
            <i class="fa-solid fa-trash-can"></i> Restaurant deleted successfully.
        </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="card bg-base-100 shadow-md border border-base-200 p-4">
        <div class="relative">
            <input type="text" id="search" placeholder="Search by restaurant name, category, or location..." class="input input-bordered w-full text-sm pl-10" />
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-base-content/40"></i>
        </div>
    </div>

    <!-- Restaurants Table -->
    <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-xs">
                <thead>
                    <tr class="font-bold uppercase bg-base-200">
                        <th>ID</th>
                        <th>Restaurant Name</th>
                        <th>Cuisine Category</th>
                        <th>Address</th>
                        <th>Created At</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="restaurant-list">
                    <?php while ($r = $restaurants->fetch_assoc()): ?>
                        <tr class="hover">
                            <td class="font-bold">#<?= $r['id']; ?></td>
                            <td class="font-bold text-sm"><?= htmlspecialchars($r['name']); ?></td>
                            <td><span class="badge badge-accent badge-sm font-semibold"><?= htmlspecialchars($r['category'] ?? 'General'); ?></span></td>
                            <td class="text-base-content/70"><?= htmlspecialchars($r['address']); ?></td>
                            <td class="text-base-content/70"><?= date('d M Y', strtotime($r['created_at'])); ?></td>
                            <td class="text-right flex items-center justify-end gap-2">
                                <a href="edit_restaurant.php?id=<?= $r['id']; ?>" class="btn btn-ghost btn-xs text-primary" title="Edit">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="manage_restaurants.php?delete_id=<?= $r['id']; ?>" onclick="return confirm('Delete this restaurant and all its menu items?');" class="btn btn-ghost btn-xs text-error" title="Delete">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($restaurants->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60 py-8">
                                No restaurants found. Click "Add New Restaurant" to add one!
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
    $('#search').on('keyup', function() {
        const search = $(this).val();
        $.get('manage_restaurants.php', { ajax: 1, search: search }, function(data) {
            $('#restaurant-list').html(data);
        });
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>