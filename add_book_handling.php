<?php
require_once 'init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Forbidden']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  exit;
}

$title = trim($_POST['title'] ?? '');
$category_id = $_POST['category_id'] ?? '';
$author = trim($_POST['author'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = trim($_POST['price'] ?? '');
$image = $_FILES['cover_image'] ?? null;

if (!$title || !$category_id || !$author || !$price || !$image || $image['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Please fill all fields and upload an image.']);
  exit;
}

$uploadDir = 'assets/images/';
$filename = basename($image['name']);
$targetPath = $uploadDir . $filename;
$dbPath = 'assets/images/' . $filename;

if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Upload directory is not writable.']);
  exit;
}

if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
  exit;
}

$stmt = $pdo->prepare("INSERT INTO books (title, category_id, author, description, price, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
$success = $stmt->execute([$title, $category_id, $author, $description, $price, $dbPath]);

if ($success) {
  echo json_encode(['success' => true, 'message' => 'Book added successfully.']);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to add book to database.']);
}
