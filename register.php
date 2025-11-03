<?php
session_start();
require_once 'includes/config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);   // Full name
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name || !$email || !$password) {
        $errors[] = 'Name, email, and password are required.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $password_md5 = md5($password); // Store MD5 (as per your table)
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_plain, password_md5, phone, address) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('ssssss', $name, $email, $password, $password_md5, $phone, $address);

        if ($stmt->execute()) {
            $success = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h2>User Registration</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST" class="w-50">
        <div class="mb-3"><input name="name" class="form-control" placeholder="Full Name" required></div>
        <div class="mb-3"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
        <div class="mb-3"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
        <div class="mb-3"><input name="phone" class="form-control" placeholder="Phone"></div>
        <div class="mb-3"><input name="address" class="form-control" placeholder="Address"></div>
        <button class="btn btn-success">Register</button>
    </form>
    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
</body>

</html>
