<?php
require_once 'init.php'; // File kết nối DB

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user']['id'])) {
    header("Location: index.php?page=home.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$province = $_POST['province'] ?? '';
$district = $_POST['district'] ?? '';
$ward = $_POST['ward'] ?? '';
$note = $_POST['note'] ?? null;
$shipping_method = $_POST['shipping'] === 'pickup' ? 'Pick-up at Store' : 'Standard Delivery';
$shipping_fee = $shipping_method === 'Pick-up at Store' ? 0.00 : 10.00;
$payment_method = $_POST['payment'] ?? 'Cash on Delivery';
$subtotal = $_POST['subtotal'] ?? 0;
$total = $_POST['total'] ?? 0;
$items = $_POST['items'] ?? [];

$full_address = "$address, $ward, $district, $province";
// echo "Processing order for user ID: $user_id<br>";
// echo "Shipping method: $shipping_method<br>";
// echo "Shipping fee: $shipping_fee<br>";
// echo "Payment method: $payment_method<br>";
// echo "Total amount: $total<br>";
// echo "Full address: $full_address<br>";
// echo "Name: $name<br>";
// echo "Phone: $phone<br>";
// echo "Items: " . print_r($items, true) . "<br>";
try {
    $pdo->beginTransaction();

    // 1. Tạo đơn hàng
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, full_name, phone, address, shipping_method, shipping_fee, payment_method, total_amount, note) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id, $name, $phone, $full_address,
        $shipping_method, $shipping_fee,
        $payment_method, $total, $note
    ]);
    $order_id = $pdo->lastInsertId();
    echo "Order ID: $order_id<br>";
    // Lấy cart_id một lần
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_id = $stmt->fetchColumn();

    if ($cart_id) {
        foreach ($items as $item) {
            $book_id = $item['book_id'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            // Ghi vào order_items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $book_id, $quantity, $price]);

            // Xóa khỏi cart_items
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND book_id = ?");
            $stmt->execute([$cart_id, $book_id]);
        }
    }

    $pdo->commit();
    header("Location: index.php?page=home.php&ordered=1");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die("Order failed: " . $e->getMessage());
}
?>
