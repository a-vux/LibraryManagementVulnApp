<?php
require_once 'init.php';

$id = $_POST['id'] ?? 0;
$username = trim($_POST['username']);
$email = trim($_POST['email']);

$check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check_stmt->execute([$email, $id]);
$existing_user = $check_stmt->fetch();

if ($existing_user) {
    header("Location: index.php?page=profile.php&id=$id&error=email_exists");
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
$stmt->execute([$username, $email, $id]);

header("Location: index.php?page=profile.php&id=$id&updated=1");
exit;
