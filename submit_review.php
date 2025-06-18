<?php
require_once 'init.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $book_id = $_POST['book_id'] ?? 0;
    $user_id = trim($_POST['user_id']);
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($book_id && $user_id && $rating && $comment) {
        $stmt = $pdo->prepare("INSERT INTO reviews (book_id, user_id, rating, comment, created_at)
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$book_id, $user_id, $rating, $comment]);
    }
    header("Location: index.php?page=book_detail.php&id=$book_id&submit=1");
    exit;
}
