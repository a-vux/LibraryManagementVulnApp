<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if (!$id || !$username || !$email) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
  }

  // Kiểm tra email đã tồn tại ở người dùng khác chưa
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
  $stmt->execute([$email, $id]);
  $existing = $stmt->fetch();

  if ($existing) {
    echo json_encode(['success' => false, 'message' => 'This email is already in use by another user.']);
    exit;
  }

  // Cập nhật thông tin
  $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
  $success = $stmt->execute([$username, $email, $id]);

  echo json_encode(['success' => $success]);
}
