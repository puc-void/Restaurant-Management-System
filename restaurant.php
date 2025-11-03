<?php
require_once 'includes/config.php';
session_start();

// Get restaurant id
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM restaurants WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();

if (!$restaurant) {
    die("Restaurant not found!");
}

// Fetch dishes
$dishes = $conn->query("SELECT * FROM foods WHERE restaurant_id=$id AND is_active=1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($restaurant['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .dish-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            cursor: pointer;
        }

        .dish-card:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }

        .dish-card img {
            transition: transform 0.4s ease, filter 0.4s ease;
        }

        .dish-card:hover img {
            transform: scale(1.1);
            filter: brightness(0.7);
        }

        .dish-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            font-weight: bold;
            font-size: 1.2rem;
            transition: opacity 0.3s ease;
        }

        .dish-card:hover .dish-overlay {
            opacity: 1;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .alert-success {
            position: sticky;
            top: 0;
            z-index: 999;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="index.php" class="btn btn-secondary">&larr; Back</a>
        <a href="cart.php" class="btn btn-primary">🛒 View Cart (<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
    </div>

    <div class="card mb-4">
        <img src="<?= htmlspecialchars($restaurant['image_url']); ?>" class="card-img-top" style="height:250px;object-fit:cover;">
        <div class="card-body">
            <h3><?= htmlspecialchars($restaurant['name']); ?></h3>
            <p><?= htmlspecialchars($restaurant['address']); ?></p>
        </div>
    </div>

    <h4>Dishes from <?= htmlspecialchars($restaurant['name']); ?></h4>
    <div class="row">
        <?php while($d = $dishes->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <a href="dish.php?id=<?= $d['id']; ?>" class="card-link">
                    <div class="card h-100 shadow-sm dish-card">
                        <img src="<?= htmlspecialchars($d['image_url']); ?>" class="card-img-top" style="height:180px;object-fit:cover;">
                        <div class="dish-overlay">$<?= number_format($d['price'],2); ?></div>
                        <div class="card-body text-center">
                            <h5><?= htmlspecialchars($d['name']); ?></h5>
                            <p class="text-muted mb-0"><?= htmlspecialchars(substr($d['description'], 0, 40)); ?>...</p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<footer class="mt-5 text-center text-muted py-3 bg-light border-top">
    <small>© <?= date('Y'); ?> void. All Rights Reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
