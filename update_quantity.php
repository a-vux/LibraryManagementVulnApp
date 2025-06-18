<?php
require_once 'init.php'; // include your DB connection
$cart_item_id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$cart_item_id || !$action || !isset($_SESSION['user']['id'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

// Check if this cart item belongs to the current user
$stmt = $pdo->prepare("SELECT ci.id, ci.quantity FROM cart_items ci
  JOIN carts c ON ci.cart_id = c.id
  WHERE ci.id = ? AND c.user_id = ?");
$stmt->execute([$cart_item_id, $_SESSION['user']['id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
  echo json_encode(['success' => false, 'message' => 'Item not found']);
  exit;
}

switch ($action) {
  case 'increase':
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
    $stmt->execute([$cart_item_id]);
    break;

  case 'decrease':
    if ($item['quantity'] > 1) {
      $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE id = ?");
      $stmt->execute([$cart_item_id]);
    }
    break;

  case 'delete':
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
    $stmt->execute([$cart_item_id]);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}
header('Content-Type: application/json');
echo json_encode(['success' => true]);
