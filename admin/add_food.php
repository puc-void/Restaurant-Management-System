<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch restaurants for dropdown
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
        $conn->query("INSERT INTO foods (restaurant_id, name, description, price, image_url, is_active) VALUES ($restaurant_id, '$name', '$description', $price, '$image_url', $is_active)");
        $success = "Food added successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Food - VOID Eats</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex">

<div class="flex-1 p-6 md:p-10 max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Add Food</h1>

    <?php if($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <input type="text" name="name" placeholder="Food Name" class="w-full p-2 border rounded" required>

        <select name="restaurant_id" class="w-full p-2 border rounded" required>
            <option value="">Select Restaurant</option>
            <?php while($r = $restaurants->fetch_assoc()): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <input type="number" step="0.01" name="price" placeholder="Price" class="w-full p-2 border rounded" required>
        <textarea name="description" placeholder="Description" class="w-full p-2 border rounded"></textarea>
        <input type="text" name="image_url" placeholder="Image URL" class="w-full p-2 border rounded">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" checked>
            <span>Active</span>
        </label>

        <button type="submit" name="submit" class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add Food</button>
        <a href="manage_foods.php" class="ml-4 px-6 py-2 bg-gray-300 rounded hover:bg-gray-400">Back</a>
    </form>
</div>
</body>
</html>
