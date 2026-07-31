<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_restaurants.php");
    exit;
}

$id = intval($_GET['id']);
$pageTitle = "Edit Restaurant #" . $id;
require_once __DIR__ . '/header.php';

$stmt_r = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt_r->bind_param("i", $id);
$stmt_r->execute();
$restaurant = $stmt_r->get_result()->fetch_assoc();

if (!$restaurant) {
    header("Location: manage_restaurants.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category'] ?? 'General');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address']);
    $opening_hours = trim($_POST['opening_hours'] ?? '10:00 AM - 10:00 PM');
    $image_url = trim($_POST['image_url']);

    if (!$name) {
        $error = "Restaurant name is required.";
    } else {
        $stmt_u = $conn->prepare("UPDATE restaurants SET name=?, category=?, phone=?, email=?, address=?, opening_hours=?, image_url=? WHERE id=?");
        $stmt_u->bind_param("sssssssi", $name, $category, $phone, $email, $address, $opening_hours, $image_url, $id);
        if ($stmt_u->execute()) {
            $success = "Restaurant updated successfully!";
            $stmt_r->execute();
            $restaurant = $stmt_r->get_result()->fetch_assoc();
        } else {
            $error = "Update failed: " . $conn->error;
        }
    }
}

$categories = ['Fast Food', 'Italian', 'Indian', 'Asian', 'Pizza', 'Desserts', 'Beverages', 'General'];
?>

<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-pen-to-square text-primary"></i> Edit Restaurant #<?= $id; ?>
            </h1>
            <p class="text-xs text-base-content/70">Modify restaurant profile, contact details, and location</p>
        </div>
        <a href="manage_restaurants.php" class="btn btn-ghost btn-sm gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Restaurants
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
            <label class="label text-xs font-bold">Restaurant Name *</label>
            <input type="text" name="name" value="<?= htmlspecialchars($restaurant['name']); ?>" required class="input input-bordered text-sm" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Cuisine Category</label>
                <select name="category" class="select select-bordered text-sm">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat); ?>" <?= ($restaurant['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-control">
                <label class="label text-xs font-bold">Opening Hours</label>
                <input type="text" name="opening_hours" value="<?= htmlspecialchars($restaurant['opening_hours'] ?? ''); ?>" placeholder="10:00 AM - 10:00 PM" class="input input-bordered text-sm" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Contact Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($restaurant['phone'] ?? ''); ?>" class="input input-bordered text-sm" />
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Contact Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($restaurant['email'] ?? ''); ?>" class="input input-bordered text-sm" />
            </div>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Address *</label>
            <textarea name="address" rows="2" class="textarea textarea-bordered text-sm"><?= htmlspecialchars($restaurant['address']); ?></textarea>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Banner Image URL</label>
            <input type="url" name="image_url" value="<?= htmlspecialchars($restaurant['image_url']); ?>" class="input input-bordered text-sm" />
            <?php if (!empty($restaurant['image_url'])): ?>
                <div class="mt-2">
                    <img src="<?= htmlspecialchars($restaurant['image_url']); ?>" alt="Preview" class="w-32 h-20 object-cover rounded-xl border border-base-300">
                </div>
            <?php endif; ?>
        </div>

        <div class="pt-4 flex gap-3">
            <button type="submit" name="submit" class="btn btn-primary shadow-lg flex-1 gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Restaurant
            </button>
            <a href="manage_restaurants.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
