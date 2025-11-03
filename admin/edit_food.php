<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);
$food = $conn->query("SELECT * FROM foods WHERE id = $id")->fetch_assoc();
if (!$food) {
    header("Location: manage_foods.php");
    exit;
}

$restaurants = $conn->query("SELECT id, name FROM restaurants");
$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $restaurant_id = intval($_POST['restaurant_id']);
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);
    $image_url = $conn->real_escape_string($_POST['image_url']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$name || !$restaurant_id || !$price) {
        $error = "Name, Restaurant, and Price are required.";
    } else {
        $conn->query("UPDATE foods SET restaurant_id=$restaurant_id, name='$name', description='$description', price=$price, image_url='$image_url', is_active=$is_active WHERE id=$id");
        $success = "Food updated successfully!";
        $food = $conn->query("SELECT * FROM foods WHERE id = $id")->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Food - VOID Eats</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; }
</style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<header class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-indigo-600">Restaurant Management System</h1>
    <a href="manage_foods.php" class="text-sm text-gray-600 hover:text-indigo-600">← Back to Manage Foods</a>
</header>
<main class="flex-1 flex justify-center items-start p-6 md:p-10">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-lg p-8 border border-gray-100">
        <h2 class="text-3xl font-semibold text-gray-800 mb-6">Edit Food Item</h2>

        <?php if($error): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg"><?= $error ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Food Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($food['name']) ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Restaurant</label>
                <select name="restaurant_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
                    <option value="">Select Restaurant</option>
                    <?php while($r = $restaurants->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>" <?= $r['id'] == $food['restaurant_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input type="number" step="0.01" name="price" value="<?= $food['price'] ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= htmlspecialchars($food['description']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                <input type="text" name="image_url" value="<?= htmlspecialchars($food['image_url']) ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <?php if (!empty($food['image_url'])): ?>
                    <div class="mt-3">
                        <img src="<?= htmlspecialchars($food['image_url']) ?>" alt="Preview" class="w-32 h-32 object-cover rounded-lg border">
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_active" <?= $food['is_active'] ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                <label class="text-gray-700">Active</label>
            </div>

            <div class="pt-4 flex justify-end space-x-4">
                <a href="manage_foods.php" class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium">Cancel</a>
                <button type="submit" name="submit" class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium">Update Food</button>
            </div>
        </form>
    </div>
</main>

<footer class="text-center text-gray-500 text-sm py-4">
    © <?= date('Y') ?> VOID. All rights reserved.
</footer>

</body>
</html>
