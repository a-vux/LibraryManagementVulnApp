<?php
require_once 'init.php';

$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
  echo json_encode(['count' => 0]);
  exit();
}

$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_id = $stmt->fetchColumn();

$count = 0;
if ($cart_id) {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE cart_id = ?");
  $stmt->execute([$cart_id]);
  $count = (int) $stmt->fetchColumn();
}

echo json_encode(['count' => $count]);
