<?php
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
  header("Location: index.php?page=home.php");
  exit();
}

$category_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-5">
  <h3 class="fw-bold mb-4">Add New Book</h3>

  <form id="addBookForm" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="cover_image" class="form-label">Book Cover</label>
      <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*" required onchange="previewImage(this)">
      <div class="mt-3">
        <img id="coverPreview" src="#" alt="Image Preview" style="max-width: 200px; display: none; border: 1px solid #ccc; padding: 4px; border-radius: 4px;" />
      </div>
    </div>
    <div class="mb-3">
      <label for="title" class="form-label">Book Name</label>
      <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
      <label for="category_id" class="form-label">Book Category</label>
      <select name="category_id" id="category_id" class="form-select" required>
        <option value="">-- Select a category --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="author" class="form-label">Book Author</label>
      <input type="text" class="form-control" id="author" name="author" required>
    </div>
    <div class="mb-3">
      <label for="price" class="form-label">Book Price</label>
      <input type="number" step="0.01" class="form-control" id="price" name="price" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Book Description</label>
      <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
    </div>
    <div class="d-flex justify-content-between">
      <a href="index.php?page=book_management.php" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-success">Add Book</button>
    </div>
  </form>
</div>

<!-- Toast -->
<div id="toast-container" class="toast-success" style="
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  display: none;
  min-width: 250px;
  padding: 15px 20px;
  color: white;
  border-radius: 6px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  animation: fadein 0.5s, fadeout 0.5s 4.5s;
">
  <span id="toast-message"></span>
  <button onclick="closeToast()" style="
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    margin-left: 15px;
    float: right;
    cursor: pointer;
  ">&times;</button>
</div>

<style>
@keyframes fadein {
  from {opacity: 0; transform: translateY(20px);}
  to {opacity: 1; transform: translateY(0);}
}
@keyframes fadeout {
  from {opacity: 1;}
  to {opacity: 0;}
}
.toast-success { background-color: #198754; }
.toast-error { background-color: #dc3545; }
</style>

<script>
function previewImage(input) {
  const preview = document.getElementById('coverPreview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = '#';
    preview.style.display = 'none';
  }
}

document.getElementById('addBookForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch('add_book_handling.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('Book added successfully.');
      setTimeout(() => window.location.href = "index.php?page=book_management.php", 2000);
    } else {
      showToast(data.message || 'Something went wrong', 'error');
    }
  })
  .catch(err => {
    console.error('Error:', err);
    showToast('Error submitting form.', 'error');
  });
});

function showToast(message, type = 'success') {
  const toast = document.getElementById('toast-container');
  const messageSpan = document.getElementById('toast-message');
  messageSpan.textContent = message;

  toast.classList.remove('toast-success', 'toast-error');
  toast.classList.add(type === 'error' ? 'toast-error' : 'toast-success');

  toast.style.display = 'block';

  setTimeout(() => {
    toast.style.display = 'none';
  }, 5000);
}

function closeToast() {
  document.getElementById('toast-container').style.display = 'none';
}
</script>
