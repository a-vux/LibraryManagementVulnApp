<?php
$email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : '';
$items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
$shipping_cost = 10; // Hardcoded shipping fee
$subtotal = 0;

foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping_cost;
?>  
<div class="container mt-5 mb-5">
  <div class="row">
    <!-- Left: Customer + Shipping + Payment -->
    <div class="col-md-7 mb-4">
      <h3 class="fw-bold mb-3">Shipping Information</h3>
      <form method="post" action="process_order.php">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" disabled />
        </div>
        <div class="mb-3">
          <label for="name" class="form-label">Full Name *</label>
          <input type="text" class="form-control" id="name" name="name" required />
        </div>
        <div class="mb-3">
          <label for="phone" class="form-label">Phone *</label>
          <input type="text" class="form-control" id="phone" name="phone" required />
        </div>
        <div class="mb-3">
          <label for="address" class="form-label">Street Address *</label>
          <input type="text" class="form-control" id="address" name="address" required />
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="province" class="form-label">Province / City</label>
            <input list="province-list" id="province" name="province" class="form-control">
  					<datalist id="province-list"></datalist>
          </div>
          <div class="col-md-4 mb-3">
            <label for="district" class="form-label">District</label>
            <input list="district-list" id="district" name="district" class="form-control" disabled>
  					<datalist id="district-list"></datalist>
          </div>
          <div class="col-md-4 mb-3">
            <label for="ward" class="form-label">Ward</label>
            <input list="ward-list" id="ward" name="ward" class="form-control" disabled>
  					<datalist id="ward-list"></datalist>
          </div>
        </div>
				<div class="mb-3">
					<label for="note" class="form-label">Notes</label>
					<textarea class="form-control" id="note" name="note" rows="3"></textarea>
				</div>
        <h5 class="mt-4 fw-bold">Shipping Method</h5>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="shipping" id="defaultShipping" checked />
          <label class="form-check-label" for="defaultShipping">
            Standard Delivery ($10)
          </label>
        </div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="shipping" id="pickupShipping" value="pickup">
					<label class="form-check-label" for="pickupShipping">
						Pick-up at Store (Free)
					</label>
				</div>

        <h5 class="mt-4 fw-bold">Payment Method</h5>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment" value="vnpay" id="vnpay" required />
          <label class="form-check-label" for="vnpay">VnPay (Visa/ATM card)</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment" value="bank" id="bank" />
          <label class="form-check-label" for="bank">Bank Transfer</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment" value="cod" id="cod" />
          <label class="form-check-label" for="cod">Cash on Delivery (COD)</label>
        </div>
        <div class="form-check mb-4">
          <input class="form-check-input" type="radio" name="payment" value="pickup" id="pickup" />
          <label class="form-check-label" for="pickup">Pick-up at store</label>
        </div>

        <!-- Hidden items -->
        <?php foreach ($items as $index => $item): ?>
          <input type="hidden" name="items[<?= $index ?>][title]" value="<?= htmlspecialchars($item['title']) ?>" />
          <input type="hidden" name="items[<?= $index ?>][price]" value="<?= $item['price'] ?>" />
          <input type="hidden" name="items[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?>" />
        <?php endforeach; ?>

        <input type="hidden" name="subtotal" value="<?= $subtotal ?>" />
        <input type="hidden" name="shipping_cost" value="<?= $shipping_cost ?>" />
        <input type="hidden" name="total" value="<?= $total ?>" />

        <button type="submit" class="btn btn-primary w-100 fw-bold">Place Order</button>
      </form>
    </div>

    <!-- Right: Order Summary -->
    <div class="col-md-5">
      <div class="bg-light p-4 rounded shadow-sm">
        <h4 class="mb-3">Order Summary (<?= count($items) ?> item<?= count($items) > 1 ? 's' : '' ?>)</h4>
        <ul class="list-group mb-3">
          <?php foreach ($items as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="d-flex">
                <img src="<?= htmlspecialchars($item['cover_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>"
                    style="width: 50px; height: auto; object-fit: cover; margin-right: 10px;">
                <div>
                  <div class="fw-bold"><?= htmlspecialchars($item['title']) ?></div>
									<div>Unit Price: $<?= number_format($item['price'], 2, ',', '.') ?></div>
                  <div>Quantity: <?= $item['quantity'] ?></div>
                </div>
            	</div>
              <span>$<?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <ul class="list-group">
          <li class="list-group-item d-flex justify-content-between">
            <span>Subtotal</span>
            <strong>$<?= number_format($subtotal, 2, ',', '.') ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Shipping</span>
            <strong id="shipping-cost-display">$<?= number_format($shipping_cost, 2, ',', '.') ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Total</span>
            <strong id="total-display">$<?= number_format($total, 2, ',', '.') ?></strong>
          </li>
        </ul>
        <a href="index.php?page=cart.php" class="btn btn-link mt-3 w-100 text-center">Back to Cart</a>
      </div>
    </div>
  </div>
</div>
<script>
const locationData = {
  'Hà Nội': {
    'Ba Đình': ['Phúc Xá', 'Trúc Bạch', 'Vĩnh Phúc', 'Cống Vị', 'Ngọc Hà'],
    'Hoàn Kiếm': ['Hàng Bạc', 'Hàng Buồm', 'Hàng Đào', 'Phan Chu Trinh', 'Tràng Tiền'],
    'Hai Bà Trưng': ['Bách Khoa', 'Bạch Mai', 'Đồng Tâm', 'Lê Đại Hành', 'Quỳnh Mai'],
    'Cầu Giấy': ['Dịch Vọng', 'Dịch Vọng Hậu', 'Mai Dịch', 'Nghĩa Đô', 'Quan Hoa'],
    'Đống Đa': ['Láng Hạ', 'Nam Đồng', 'Phương Mai', 'Trung Liệt', 'Thịnh Quang']
  },
  'Hồ Chí Minh': {
    'Quận 1': ['Bến Nghé', 'Bến Thành', 'Cô Giang', 'Cầu Kho', 'Nguyễn Thái Bình'],
    'Quận 3': ['Phường 1', 'Phường 2', 'Phường 4', 'Phường 5', 'Phường 6'],
    'Bình Thạnh': ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 5', 'Phường 7'],
    'Phú Nhuận': ['Phường 1', 'Phường 2', 'Phường 3', 'Phường 5', 'Phường 7'],
    'Tân Bình': ['Phường 1', 'Phường 2', 'Phường 4', 'Phường 5', 'Phường 6']
  },
  'Đà Nẵng': {
    'Hải Châu': ['Thạch Thang', 'Hải Châu 1', 'Hải Châu 2', 'Nam Dương', 'Phước Ninh'],
    'Thanh Khê': ['Tam Thuận', 'Thanh Khê Đông', 'Thanh Khê Tây', 'Vĩnh Trung', 'Tân Chính'],
    'Sơn Trà': ['An Hải Bắc', 'An Hải Đông', 'An Hải Tây', 'Mân Thái', 'Phước Mỹ'],
    'Ngũ Hành Sơn': ['Khuê Mỹ', 'Hòa Hải', 'Hòa Quý', 'Mỹ An', 'Bắc Mỹ An'],
    'Liên Chiểu': ['Hòa Khánh Bắc', 'Hòa Khánh Nam', 'Hòa Minh', 'Hòa Hiệp Bắc', 'Hòa Hiệp Nam']
  },
  'Cần Thơ': {
    'Ninh Kiều': ['Tân An', 'An Cư', 'An Hòa', 'An Khánh', 'An Nghiệp'],
    'Bình Thủy': ['Bình Thủy', 'Trà Nóc', 'Trà An', 'Long Hòa', 'Long Tuyền'],
    'Cái Răng': ['Hưng Phú', 'Hưng Thạnh', 'Lê Bình', 'Phú Thứ', 'Tân Phú'],
    'Thốt Nốt': ['Thốt Nốt', 'Tân Hưng', 'Thới Thuận', 'Thạnh Hòa', 'Thạnh Phước'],
    'Ô Môn': ['Châu Văn Liêm', 'Long Hưng', 'Phước Thới', 'Thới An', 'Trường Lạc']
  },
  'Hải Phòng': {
    'Ngô Quyền': ['Cầu Đất', 'Cầu Tre', 'Đằng Giang', 'Lạc Viên', 'Lê Lợi'],
    'Lê Chân': ['An Biên', 'An Dương', 'Cát Dài', 'Dư Hàng', 'Dư Hàng Kênh'],
    'Hồng Bàng': ['Hạ Lý', 'Hoàng Văn Thụ', 'Minh Khai', 'Phan Bội Châu', 'Quán Toan'],
    'Kiến An': ['Bắc Sơn', 'Nam Sơn', 'Ngọc Sơn', 'Quán Trữ', 'Trần Thành Ngọ'],
    'Dương Kinh': ['Đa Phúc', 'Hưng Đạo', 'Hải Thành', 'Hòa Nghĩa', 'Tân Thành']
  }
};

const provinceInput = document.getElementById('province');
const districtInput = document.getElementById('district');
const wardInput = document.getElementById('ward');
const provinceList = document.getElementById('province-list');
const districtList = document.getElementById('district-list');
const wardList = document.getElementById('ward-list');

// Populate province list
Object.keys(locationData).forEach(province => {
  const option = document.createElement('option');
  option.value = province;
  provinceList.appendChild(option);
});

// event listener cho tinh / thanh pho
provinceInput.addEventListener('input', function () {
  const selectedProvince = this.value;
  districtInput.disabled = true;
  wardInput.disabled = true;
  districtList.innerHTML = '';
  wardList.innerHTML = '';
  districtInput.value = '';
  wardInput.value = '';

  if (locationData[selectedProvince]) {
    Object.keys(locationData[selectedProvince]).forEach(district => {
      const option = document.createElement('option');
      option.value = district;
      districtList.appendChild(option);
    });
    districtInput.disabled = false;
  }
});

// event listener cho quan / huyen
districtInput.addEventListener('input', function () {
  const province = provinceInput.value;
  const district = this.value;
  wardInput.disabled = true;
  wardList.innerHTML = '';
  wardInput.value = '';

  if (locationData[province] && locationData[province][district]) {
    locationData[province][district].forEach(ward => {
      const option = document.createElement('option');
      option.value = ward;
      wardList.appendChild(option);
    });
    wardInput.disabled = false;
  }
});

// event listener cho shipping method
document.addEventListener('DOMContentLoaded', function () {
  const shippingRadios = document.querySelectorAll('input[name="shipping"]');
  const shippingInput = document.querySelector('input[name="shipping_cost"]');
	const subtotal = parseFloat(<?= $subtotal ?>);
  const shippingCostDisplay = document.getElementById('shipping-cost-display');
  const totalDisplay = document.getElementById('total-display');

	function formatCurrency(amount) {
    return '$' + amount.toFixed(2).replace('.', ',');
  }

  function updateShippingCost() {
    const selected = document.querySelector('input[name="shipping"]:checked').value;
    const cost = selected === 'pickup' ? 0 : 10;

    // Cập nhật hidden input
    shippingInput.value = cost;
		shippingCostDisplay.textContent = formatCurrency(cost);
    totalDisplay.textContent = formatCurrency(subtotal + cost);
  }

  shippingRadios.forEach(radio => {
    radio.addEventListener('change', updateShippingCost);
  });

  updateShippingCost(); // set initial value
});
</script>
