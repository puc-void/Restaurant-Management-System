<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_foods.php");
    exit;
}

$id = intval($_GET['id']);
$pageTitle = "Edit Dish #" . $id;
require_once __DIR__ . '/header.php';

$stmt_f = $conn->prepare("SELECT * FROM foods WHERE id = ?");
$stmt_f->bind_param("i", $id);
$stmt_f->execute();
$food = $stmt_f->get_result()->fetch_assoc();

if (!$food) {
    header("Location: manage_foods.php");
    exit;
}

$restaurants = $conn->query("SELECT id, name FROM restaurants ORDER BY name ASC");
$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $restaurant_id = intval($_POST['restaurant_id']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category'] ?? 'Main Course');
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$restaurant_id || !$price) {
        $error = "Name, Restaurant, and Price are required.";
    } else {
        $stmt_u = $conn->prepare("UPDATE foods SET restaurant_id=?, name=?, category=?, description=?, price=?, image_url=?, is_active=? WHERE id=?");
        $stmt_u->bind_param("isssdsii", $restaurant_id, $name, $category, $description, $price, $image_url, $is_active, $id);
        if ($stmt_u->execute()) {
            $success = "Dish updated successfully!";
            $stmt_f->execute();
            $food = $stmt_f->get_result()->fetch_assoc();
        } else {
            $error = "Update failed: " . $conn->error;
        }
    }
}
$categories = ['Main Course', 'Fast Food', 'Pizza', 'Italian', 'Indian', 'Asian', 'Desserts', 'Beverages'];
?>

<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-pen-to-square text-primary"></i> Edit Dish #<?= $id; ?>
            </h1>
            <p class="text-xs text-base-content/70">Modify dish details, pricing, and availability</p>
        </div>
        <a href="manage_foods.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Dishes
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error text-white shadow-md text-xs">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success shadow-md text-xs">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <form method="post" class="card bg-base-100 shadow-xl border border-base-200 p-6 md:p-8 space-y-4">
        <div class="form-control">
            <label class="label text-xs font-bold">Dish Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($food['name']); ?>" required class="input input-bordered text-sm" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Select Restaurant *</label>
                <select name="restaurant_id" required class="select select-bordered text-sm">
                    <option value="">Select Restaurant</option>
                    <?php while ($r = $restaurants->fetch_assoc()): ?>
                        <option value="<?= $r['id']; ?>" <?= $r['id'] == $food['restaurant_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($r['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Category</label>
                <select name="category" class="select select-bordered text-sm">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat); ?>" <?= ($food['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Price ($) *</label>
            <input type="number" step="0.01" name="price" value="<?= $food['price']; ?>" required class="input input-bordered text-sm font-bold" />
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Description</label>
            <textarea name="description" rows="3" class="textarea textarea-bordered text-sm"><?= htmlspecialchars($food['description']); ?></textarea>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Image URL</label>
            <input type="url" name="image_url" value="<?= htmlspecialchars($food['image_url']); ?>" class="input input-bordered text-sm" />
            <?php if (!empty($food['image_url'])): ?>
                <div class="mt-2">
                    <img src="<?= htmlspecialchars($food['image_url']); ?>" alt="Preview" class="w-24 h-24 object-cover rounded-xl border border-base-300">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="is_active" <?= $food['is_active'] ? 'checked' : ''; ?> class="checkbox checkbox-primary" />
                <span class="label-text font-bold text-xs">Active & Available for Order</span>
            </label>
        </div>

        <div class="pt-4 flex gap-3">
            <button type="submit" name="submit" class="btn btn-primary shadow-lg flex-1 gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Dish
            </button>
            <a href="manage_foods.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
