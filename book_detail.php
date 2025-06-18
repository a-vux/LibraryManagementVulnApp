<?php
$book_id = $_GET['id'] ?? 0;
$sql = "SELECT b.*, c.name AS category_name 
        FROM books b 
        JOIN categories c ON b.category_id = c.id 
        WHERE b.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$book) {
    echo "<div class='container mt-5'><h2>Book not found.</h2></div>";
    return;
}

$reviews = $pdo->prepare("SELECT r.*, u.username 
                          FROM reviews r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.book_id = ? 
                          ORDER BY r.created_at DESC");
$reviews->execute([$book_id]);
$reviews = $reviews->fetchAll(PDO::FETCH_ASSOC);

$avg_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE book_id = ?");
$avg_stmt->execute([$book_id]);
$avg_rating = round($avg_stmt->fetchColumn(), 1); // Làm tròn 1 chữ số thập phân

$inv_stmt = $pdo->prepare("SELECT quantity FROM inventory WHERE book_id = :id");
$inv_stmt->execute([':id' => $book_id]);
$inventory = $inv_stmt->fetch(PDO::FETCH_ASSOC);
$stock = $inventory ? $inventory['quantity'] : 0;
?>
<style>
.book-cover {
    width: 100%;
    max-width: 400px;
    height: auto;
    object-fit: cover;
    border: 1px solid #ccc;
    box-shadow: 2px 2px 6px rgba(0,0,0,0.1);
}
.star-rating {
    display: inline-block;
    font-size: 1.5rem;
    position: relative;
    unicode-bidi: bidi-override;
    color: #ccc;
}
.star-rating::before {
    content: "★★★★★";
}
.star-rating::after {
    content: "★★★★★";
    position: absolute;
    top: 0;
    left: 0;
    overflow: hidden;
    color: gold;
    white-space: nowrap;
    width: calc(var(--percent, 0%) * 1);
}
#quantityInput {
    max-width: 100px;
    text-align: center;
    font-size: 18px;
    -moz-appearance: textfield;
}
.input-group .btn {
    width: 60px;
}
/* for toast */
.toast {
    position: fixed;
    bottom: 30px;
    right: -400px;
    background-color: #28a745;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    z-index: 9999;
    transition: right 0.5s ease-in-out, opacity 0.3s ease-in-out;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-width: 250px;
    opacity: 0;
}

.toast.show {
    right: 30px;
    opacity: 1;
}
.toast.hide {
    right: -400px;
    opacity: 0;
}
.toast.error {
    background-color: #dc3545; /* đỏ */
}

.toast .close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    margin-left: 15px;
    cursor: pointer;
}
</style>
<!-- Toast up review thanh cong -->
<?php if (isset($_GET['submit'])): ?>
<div id="toast" class="toast show">
  <div class="toast-content">
    <span>✅ Upload review successfully</span>
    <button onclick="closeToast()" class="close-btn">&times;</button>
  </div>
</div>
<?php endif; ?>
<div class="container mt-5">
    <div class="row">
        <!-- Cover image -->
        <div class="col-md-4">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="Cover" class="img-fluid book-cover">
        </div>

        <!-- Book info -->
        <div class="col-md-8">
            <h3><?= htmlspecialchars($book['title']) ?></h3>
            <p><strong>By:</strong> <?= htmlspecialchars($book['author']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($book['category_name'] ?? 'Unknown') ?></p>
            <p><strong>Price:</strong> <span class="text-danger fs-4"><?= number_format($book['price'], 2, ',', '.') . "$" ?></span></p>
            <p><strong>Availability:</strong> <?= $stock > 0 ? $stock : "<span class='text-danger'>Out of stock</span>" ?></p>
            <?php if ($avg_rating > 0): ?>
                <p>
                    <strong>Average Rating:</strong>
                    <span class="star-rating" style="--percent: <?= ($avg_rating / 5) * 100 ?>%;">
                    </span>
                    <span class="ms-2">(<?= $avg_rating ?>/5)</span>
                </p>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label fw-bold">QUANTITY</label>
                <div class="input-group" style="max-width: 500px;">
                    <button class="btn btn-outline-secondary" type="button" onclick="changeQty(-1)">-</button>
                    <input type="number" class="form-control text-center" id="quantityInput" name="quantity" value="1" min="1">
                    <button class="btn btn-outline-secondary" type="button" onclick="changeQty(1)">+</button>
                </div>
            </div>
            <a href="#" class="btn btn-danger">Buy Now</a>
            <a href="#" class="btn btn-warning" onclick="addToCart()">Add to cart</a>
        </div>
    </div>
    <script>
        function changeQty(amount) {
            const input = document.getElementById('quantityInput');
            let value = parseInt(input.value);
            if (!isNaN(value)) {
                value += amount;
                if (value < 1) value = 1;
                input.value = value;
            }
        }
        function addToCart() {
            const bookId = <?= $book_id ?>;
            const quantity = document.getElementById('quantityInput').value;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `book_id=${bookId}&quantity=${quantity}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateHeaderCartCount();
                    updateCartDropdown();
                    showToast('🛒 Added to cart successfully');
                } else {
                    alert(data.message || 'Failed to add to cart.');
                }
            });
        }
    </script>

    <!-- Overview -->
    <div class="mt-5">
        <h4 class="text-uppercase text-danger">Book Overview</h4>
        <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
    </div>

    <!-- Reviews -->
    <div class="mt-5">
        <h4 class="mb-3">Customer Reviews</h4>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="border p-3 mb-3">
                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                    <span class="text-warning">
                        <?= str_repeat('★', $review['rating']) ?>
                        <?= str_repeat('☆', 5 - $review['rating']) ?>
                    </span>
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <small><?= htmlspecialchars($review['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Submit Review -->
    <div class="mt-5">
        <h4>Leave a Review</h4>
        <form method="POST" action="submit_review.php">
            <input type="hidden" name="book_id" value="<?= $book_id ?>">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user']['id']?>">
            <div class="mb-3">
                <label>Rating:</label>
                <select name="rating" class="form-select w-25">
                    <option value="5">★★★★★</option>
                    <option value="4">★★★★☆</option>
                    <option value="3">★★★☆☆</option>
                    <option value="2">★★☆☆☆</option>
                    <option value="1">★☆☆☆☆</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Review:</label>
                <textarea name="comment" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>
</div>
<script>
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast show';
    toast.innerHTML = `
        <div class="toast-content">
            ${message}
            <button onclick="closeToast()></button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
// cho up review toast
function closeToast() {
    const toast = document.getElementById('toast');
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 500); // cleanup
}

window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => closeToast(), 5000); // auto-close in 5s
    }
});
</script>