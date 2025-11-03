<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $description = $conn->real_escape_string($_POST['description']);
    $image_url = $conn->real_escape_string($_POST['image_url']);

    if (!$name) {
        $error = "Restaurant name is required.";
    } else {
        $conn->query("INSERT INTO restaurants (name, address, description, image_url) VALUES ('$name', '$address', '$description', '$image_url')");
        $success = "Restaurant added successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Restaurant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex">

    <div class="flex-1 p-6 md:p-10 max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Add Restaurant</h1>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <input type="text" name="name" placeholder="Restaurant Name" class="w-full p-2 border rounded" required>
            <input type="text" name="address" placeholder="Address" class="w-full p-2 border rounded">
            <textarea name="description" placeholder="Description" class="w-full p-2 border rounded"></textarea>
            <input type="text" name="image_url" placeholder="Image URL" class="w-full p-2 border rounded">
            <button type="submit" name="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add Restaurant</button>
            <a href="manage_restaurants.php" class="ml-4 px-6 py-2 bg-gray-300 rounded hover:bg-gray-400">Back</a>
        </form>
    </div>

</body>

</html>