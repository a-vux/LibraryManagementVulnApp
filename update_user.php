<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');

  // Trường không hợp lệ
  if (!$id || !$username || !$email) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
  }

  // Kiểm tra email đã tồn tại ở người dùng khác chưa
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
  $stmt->execute([$email, $id]);
  $existing = $stmt->fetch();

  if ($existing) {
    http_response_code(409); // Conflict
    echo json_encode(['success' => false, 'message' => 'This email is already in use by another user.']);
    exit;
  }

  // Cập nhật thông tin
  $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
  $success = $stmt->execute([$username, $email, $id]);

  if ($success) {
    http_response_code(200); // OK
    echo json_encode(['success' => true]);
  } else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Failed to update user.']);
  }
}
