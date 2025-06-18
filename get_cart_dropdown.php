<?php
require 'init.php';

$user_id = $_SESSION['user']['id'] ?? null;
$cart_items = [];

if ($user_id) {
    $stmt = $pdo->prepare("
        SELECT b.id AS book_id, b.title, b.price, b.cover_image, ci.quantity
        FROM carts c
        JOIN cart_items ci ON c.id = ci.cart_id
        JOIN books b ON ci.book_id = b.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

ob_start();
?>

<?php if (count($cart_items) > 0): ?>
  <h4 class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-primary">Your cart</span>
    <span class="badge bg-primary rounded-pill" id="header-cart-count"><?= count($cart_items) ?></span>
  </h4>
  <ul class="list-group mb-3">
    <?php $subtotal = 0; foreach ($cart_items as $item):
      $subtotal += $item['price'] * $item['quantity'];
    ?>
    <li class="list-group-item bg-transparent d-flex justify-content-between lh-sm align-items-start">
      <div class="d-flex">
        <img src="<?= htmlspecialchars($item['cover_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>"
            style="width: 50px; height: auto;" class="me-2" />
        <div class="flex-grow-1">
          <h6 class="mb-1"><?= htmlspecialchars($item['title']) ?></h6>
          <small>Quantity: <?= $item['quantity'] ?></small>
        </div>
      </div>
      <span class="text-primary">$<?= number_format($item['price'], 2, ',', '.') ?></span>
    </li>
    <?php endforeach; ?>
    <li class="list-group-item bg-transparent d-flex justify-content-between">
      <span class="text-capitalize"><b>Sub-Total</b></span>
      <strong class="text-danger">$<?= number_format($subtotal, 2, ',', '.') ?></strong>
    </li>
  </ul>
  <div class="d-flex flex-wrap justify-content-center">
    <a href="index.php?page=cart.php" class="w-100 btn btn-dark">View Cart</a>
  </div>
<?php else: ?>
  <p class="text-center mb-0">Your cart is empty.</p>
<?php endif; ?>

<?php
$html = ob_get_clean();
echo $html;
