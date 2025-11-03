<?php
require_once 'includes/config.php';

$search = trim($_GET['q'] ?? '');
$likeSearch = "%$search%";

if (!$search) {
    echo json_encode(['restaurants' => [], 'dishes' => []]);
    exit;
}
$stmt = $conn->prepare("
    SELECT DISTINCT r.* 
    FROM restaurants r
    LEFT JOIN foods f ON f.restaurant_id = r.id AND f.is_active = 1
    WHERE r.name LIKE ? OR f.name LIKE ?
    ORDER BY r.id DESC
");
$stmt->bind_param('ss', $likeSearch, $likeSearch);
$stmt->execute();
$restaurants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2 = $conn->prepare("
    SELECT f.*, r.name AS restaurant_name
    FROM foods f
    JOIN restaurants r ON f.restaurant_id = r.id
    WHERE f.is_active = 1 AND (f.name LIKE ? OR r.name LIKE ?)
    ORDER BY f.id DESC
");
$stmt2->bind_param('ss', $likeSearch, $likeSearch);
$stmt2->execute();
$dishes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'restaurants' => $restaurants,
    'dishes' => $dishes
]);
