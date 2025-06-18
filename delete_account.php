<?php
require_once 'init.php';

$id = $_POST['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);
session_destroy();
header("Location: index.php?page=home.php&deleted=1");
exit;
