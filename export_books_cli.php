<?php
require_once 'init.php'; // Kết nối PDO

$output = fopen('php://stdout', 'w'); // hoặc php://output nếu cần

// Ghi dòng tiêu đề
fputcsv($output, ['ID', 'Title', 'Author', 'Category', 'Price', 'Cover Image']);

// Truy vấn dữ liệu
$stmt = $pdo->query("SELECT b.id, b.title, b.author, b.price, c.name AS category, b.cover_image
                     FROM books b
                     LEFT JOIN categories c ON b.category_id = c.id
                     ORDER BY b.title");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['title'],
        $row['author'],
        $row['category'],
        number_format($row['price'], 2, '.', ''),
        $row['cover_image']
    ]);
}

fclose($output);
