<?php
// Get current user ID
$user_id = $_SESSION['user']['id'] ?? null;
if (!$user_id) {
  header("Location: login.php");
  exit();
}

// Get cart and cart items from database
$stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$cart_id = $stmt->fetchColumn();

$cart_items = [];
if ($cart_id) {
  $stmt = $pdo->prepare("SELECT ci.id AS cart_item_id, b.title, b.cover_image, b.price, ci.quantity FROM cart_items ci
    JOIN books b ON ci.book_id = b.id WHERE ci.cart_id = ?");
  $stmt->execute([$cart_id]);
  $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container my-5">
  <h3>Cart (<span id="cart-count"><?= count($cart_items) ?></span> items)</h3>
  <div class="row">
    <!-- Left side: Cart Items -->
    <div class="col-md-8">
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="select-all">
        <label id="select-all-label" class="form-check-label" for="select-all">Select All (<?= count($cart_items) ?> items)</label>
      </div>
      <?php foreach ($cart_items as $item): ?>
      <div class="card mb-3 cart-item" data-item-id="<?= $item['cart_item_id'] ?>" data-price="<?= $item['price'] ?>">
        <div class="card-body d-flex align-items-center">
          <input class="form-check-input me-3 item-checkbox" type="checkbox" checked>
          <img src="<?= htmlspecialchars($item['cover_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="width: 80px; height: auto; margin-right: 15px;">
          <div class="flex-grow-1">
            <div><strong style="font-weight: 700"><?= htmlspecialchars($item['title']) ?></strong></div>
            <div>Price: $<?= number_format($item['price'], 2, ',', '.') ?></div>
          </div>
          <div class="d-flex align-items-center mx-3">
            <button class="btn btn-sm btn-outline-secondary btn-decrease" data-id="<?= $item['cart_item_id'] ?>" type="button">−</button>
            <input type="text" class="form-control form-control-sm text-center mx-1 quantity-input" value="<?= $item['quantity'] ?>" style="width: 80px;" readonly>
            <button class="btn btn-sm btn-outline-secondary btn-increase" data-id="<?= $item['cart_item_id'] ?>" type="button">+</button>
          </div>
          <div class="text-danger fw-bold mx-3 item-total">
            $<?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?>
          </div>
          <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $item['cart_item_id'] ?>">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Right side: Total and Checkout -->
    <div class="col-md-4">
      <div class="card p-3">
        <h5>Total</h5>
        <div class="d-flex justify-content-between">
          <span>Total (selected):</span>
          <strong id="total-price" class="text-danger">$0</strong>
        </div>
        <button id="checkout-btn" class="btn btn-primary mt-3 w-100">Checkout</button>
      </div>
    </div>
  </div>
</div>

<script>
function updateCartCount() {
  const count = document.querySelectorAll('.cart-item').length;
  document.getElementById('cart-count').textContent = count;
  document.getElementById('select-all-label').textContent = `Select All (${count} items)`;

}

document.addEventListener('DOMContentLoaded', function () {

  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.cart-item').forEach(item => {
      const checkbox = item.querySelector('.item-checkbox');
      const quantity = parseInt(item.querySelector('.quantity-input').value);
      const price = parseFloat(item.dataset.price);
      if (checkbox.checked) total += price * quantity;
    });
    document.getElementById('total-price').textContent = '$' + total.toLocaleString('en-US');
  }

  function updateItemTotal(item) {
    const quantity = parseInt(item.querySelector('.quantity-input').value);
    const price = parseFloat(item.dataset.price);
    item.querySelector('.item-total').textContent = '$' + (price * quantity).toLocaleString('en-US');
  }

  // Tăng số lượng
  document.querySelectorAll('.btn-increase').forEach(btn => {
    btn.addEventListener('click', function () {
      const itemId = this.dataset.id;
      const item = this.closest('.cart-item');
      fetch('update_quantity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=increase&id=${itemId}`
      })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          const input = item.querySelector('.quantity-input');
          input.value = parseInt(input.value) + 1;
          updateItemTotal(item);
          updateTotal();
          updateHeaderCartCount();
          updateCartDropdown();
        }
      })
      .catch(err => console.error('Error:', err));
    });
  });

  // Giảm số lượng
  document.querySelectorAll('.btn-decrease').forEach(btn => {
    btn.addEventListener('click', function () {
      const itemId = this.dataset.id;
      const item = this.closest('.cart-item');
      const input = item.querySelector('.quantity-input');
      const currentQty = parseInt(input.value);
      if (currentQty <= 1) return;

      fetch('update_quantity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=decrease&id=${itemId}`
      })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          input.value = currentQty - 1;
          updateItemTotal(item);
          updateTotal();
          updateHeaderCartCount();
          updateCartDropdown();
        }
      })
      .catch(err => console.error('Error:', err));
    });
  });

  // Xoá item
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
      const itemId = this.dataset.id;
      const item = this.closest('.cart-item');
      fetch('update_quantity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&id=${itemId}`
      })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          item.remove(); // xoá phần tử khỏi UI
          updateTotal();
          updateCartCount();
          updateHeaderCartCount();
          updateCartDropdown();
        }
      })
      .catch(err => console.error('Error:', err));
    });
  });

  // Chọn/Bỏ từng item
  document.querySelectorAll('.item-checkbox').forEach(cb =>
    cb.addEventListener('change', updateTotal)
  );

  // Chọn tất cả
  document.getElementById('select-all').addEventListener('change', function () {
    const checked = this.checked;
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = checked);
    updateTotal();
  }); 

  // Khởi tạo tổng tiền ban đầu
  updateTotal();
});
</script>
