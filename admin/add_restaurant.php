<?php
$pageTitle = "Add Restaurant";
require_once __DIR__ . '/header.php';

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
        $stmt = $conn->prepare("INSERT INTO restaurants (name, category, phone, email, address, opening_hours, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $category, $phone, $email, $address, $opening_hours, $image_url);
        if ($stmt->execute()) {
            $success = "Restaurant added successfully!";
        } else {
            $error = "Failed to add restaurant: " . $conn->error;
        }
    }
}

$categories = ['Fast Food', 'Italian', 'Indian', 'Asian', 'Pizza', 'Desserts', 'Beverages', 'General'];
?>

<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold font-heading flex items-center gap-3">
                <i class="fa-solid fa-plus text-primary"></i> Add New Restaurant
            </h1>
            <p class="text-xs text-base-content/70">Register a new partner restaurant in GourmetHub</p>
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
            <input type="text" name="name" required placeholder="e.g. Gourmet Burger Haven" class="input input-bordered text-sm" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Cuisine Category</label>
                <select name="category" class="select select-bordered text-sm">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat); ?>"><?= htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-control">
                <label class="label text-xs font-bold">Opening Hours</label>
                <input type="text" name="opening_hours" placeholder="10:00 AM - 10:00 PM" class="input input-bordered text-sm" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label text-xs font-bold">Contact Phone</label>
                <input type="text" name="phone" placeholder="+880 1700-000000" class="input input-bordered text-sm" />
            </div>

            <div class="form-control">
                <label class="label text-xs font-bold">Contact Email</label>
                <input type="email" name="email" placeholder="contact@restaurant.com" class="input input-bordered text-sm" />
            </div>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Address *</label>
            <textarea name="address" rows="2" placeholder="Full street address & location" class="textarea textarea-bordered text-sm"></textarea>
        </div>

        <div class="form-control">
            <label class="label text-xs font-bold">Banner / Cover Image URL</label>
            <input type="url" name="image_url" placeholder="https://images.unsplash.com/..." class="input input-bordered text-sm" />
        </div>

        <div class="pt-4 flex gap-3">
            <button type="submit" name="submit" class="btn btn-primary shadow-lg flex-1 gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Save Restaurant
            </button>
            <a href="manage_restaurants.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>