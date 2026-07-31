<?php
// Shared Helper Functions for Restaurant Management System

if (!function_exists('get_cart_count')) {
    function get_cart_count() {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            return 0;
        }
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += isset($item['quantity']) ? intval($item['quantity']) : 1;
        }
        return $count;
    }
}

if (!function_exists('get_cart_total')) {
    function get_cart_total() {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            return 0.00;
        }
        $total = 0.00;
        foreach ($_SESSION['cart'] as $item) {
            $price = isset($item['price']) ? floatval($item['price']) : 0;
            $qty = isset($item['quantity']) ? intval($item['quantity']) : 1;
            $total += $price * $qty;
        }
        return $total;
    }
}

if (!function_exists('get_food_rating')) {
    function get_food_rating($conn, $food_id) {
        $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE food_id = ?");
        $stmt->bind_param("i", $food_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return [
            'avg' => $res['avg_rating'] ? round($res['avg_rating'], 1) : 5.0,
            'count' => $res['count'] ?? 0
        ];
    }
}

if (!function_exists('get_restaurant_rating')) {
    function get_restaurant_rating($conn, $restaurant_id) {
        $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE restaurant_id = ? OR food_id IN (SELECT id FROM foods WHERE restaurant_id = ?)");
        $stmt->bind_param("ii", $restaurant_id, $restaurant_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return [
            'avg' => $res['avg_rating'] ? round($res['avg_rating'], 1) : 5.0,
            'count' => $res['count'] ?? 0
        ];
    }
}
