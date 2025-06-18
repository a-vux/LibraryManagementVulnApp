<?php
require_once 'init.php';

$user_id = $_SESSION['user']['id'] ?? null;
$book_id = $_POST['book_id'] ?? 0;
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

if (!$user_id || !$book_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// 1. Get user's cart
$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Create cart if not exists
if (!$cart) {
    $stmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $cart_id = $pdo->lastInsertId();
} else {
    $cart_id = $cart['id'];
}

// 3. Check if book already in cart
$stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND book_id = ?");
$stmt->execute([$cart_id, $book_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    // 4. If exists, increase quantity
    $new_quantity = $item['quantity'] + $quantity;
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $stmt->execute([$new_quantity, $item['id']]);
} else {
    // 5. Else, insert new row
    $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, book_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$cart_id, $book_id, $quantity]);
}

// 6. Return success
echo json_encode(['success' => true]);
