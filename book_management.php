<?php
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
  header("Location: index.php?page=home.php");
  exit();
}

$stmt = $pdo->query("SELECT b.id, b.title, b.author, b.price, b.cover_image, c.name AS category
                     FROM books b
                     LEFT JOIN categories c ON b.category_id = c.id
                     ORDER BY b.title");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
.modal-overlay {
  display: none; position: fixed; top: 0; left: 0;
  width: 100vw; height: 100vh;
  background-color: rgba(0,0,0,0.5); z-index: 9998;
  justify-content: center; align-items: center;
}
.modal-content {
  background-color: white; padding: 30px;
  border-radius: 12px; text-align: center;
  max-width: 400px; width: 90%;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
  from { transform: translateY(-40px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.close-modal {
  position: absolute; top: 8px; right: 12px;
  background: none; border: none;
  font-size: 24px; color: #888; cursor: pointer;
}
.toast-success { background-color: #198754; }
.toast-error { background-color: #dc3545; }
</style>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Book Lists</h3>
    <a href="#" class="btn btn-primary">Add New Book</a>
    <a href="#" class="btn btn-outline-success" id="exportBtn">Export Book</a>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-success text-uppercase">
        <tr>
          <th>No</th>
          <th>Book Cover</th>
          <th>Book Name</th>
          <th>Category</th>
          <th>Author</th>
          <th>Price</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($books as $index => $book): ?>
        <tr data-book-id="<?= $book['id'] ?>">
          <td><?= $index + 1 ?></td>
          <td><img src="<?= htmlspecialchars($book['cover_image']) ?>" style="width: 60px;"></td>
          <td class="title-cell">
            <span class="static"><?= htmlspecialchars($book['title']) ?></span>
            <input class="form-control d-none edit-input" name="title" value="<?= htmlspecialchars($book['title']) ?>">
          </td>
          <td class="category-cell">
            <span class="static"><?= htmlspecialchars($book['category']) ?></span>
            <input class="form-control d-none edit-input" name="category" value="<?= htmlspecialchars($book['category']) ?>">
          </td>
          <td class="author-cell">
            <span class="static"><?= htmlspecialchars($book['author']) ?></span>
            <input class="form-control d-none edit-input" name="author" value="<?= htmlspecialchars($book['author']) ?>">
          </td>
          <td class="price-cell">
            <span class="static">$<?= number_format($book['price'], 2, ',', '.') ?></span>
            <input class="form-control d-none edit-input" name="price" value="<?= $book['price'] ?>">
          </td>
          <td class="action-cell">
            <button class="btn btn-sm btn-primary btn-edit me-2"><i class="bi bi-pencil-fill"></i></button>
            <button class="btn btn-sm btn-delete" onclick="openDeleteBookModal()" data-id="<?= $book['id'] ?>"><i class="bi bi-trash-fill"></i></button>
            <button class="btn btn-sm btn-success btn-save d-none me-2">Save</button>
            <button class="btn btn-sm btn-secondary btn-cancel d-none">Cancel</button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($books)): ?>
          <tr><td colspan="7" class="text-center">No books found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteBookModal" class="modal-overlay">
  <div class="modal-content">
    <button class="close-modal" onclick="closeDeleteBookModal()">&times;</button>
    <p class="modal-text">Are you sure to delete this book?</p>
    <div class="modal-buttons">
      <button type="button" id="confirmDeleteBookBtn" class="btn btn-danger">Yes</button>
      <button type="button" class="btn btn-secondary" onclick="closeDeleteBookModal()">No</button>
    </div>
  </div>
</div>

<!-- Export Modal -->
<div id="exportBookModal" class="modal-overlay">
  <div class="modal-content position-relative">
    <button class="close-modal" onclick="closeExportBookModal()">&times;</button>
    <h5 class="mb-3 fw-bold">Export Book List</h5>
    <div class="mb-3">
      <input type="text" id="exportFilename" class="form-control" placeholder="Enter file name (e.g., books.csv)">
    </div>
    <div class="modal-buttons">
      <button type="button" class="btn btn-success" id="confirmExportBtn">Export</button>
      <button type="button" class="btn btn-secondary" onclick="closeExportBookModal()">Cancel</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast-container" class="toast-success" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: none; min-width: 250px; padding: 15px 20px; color: white; border-radius: 6px; box-shadow: 0 4px 8px rgba(0,0,0,0.15); animation: fadein 0.5s, fadeout 0.5s 4.5s;">
  <span id="toast-message"></span>
  <button onclick="closeToast()" style="background: none; border: none; color: white; font-size: 18px; margin-left: 15px; float: right; cursor: pointer;">&times;</button>
</div>
<style>
@keyframes fadein { from {opacity: 0; transform: translateY(20px);} to {opacity: 1; transform: translateY(0);} }
@keyframes fadeout { from {opacity: 1;} to {opacity: 0;} }
</style>

<script>
let bookIdToDelete = null;
let rowToDelete = null;

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
      bookIdToDelete = this.dataset.id;
      rowToDelete = this.closest('tr');
      openDeleteBookModal();
    });
  });

  document.getElementById('confirmDeleteBookBtn').addEventListener('click', function () {
    fetch('delete_book.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `book_id=${encodeURIComponent(bookIdToDelete)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (rowToDelete) rowToDelete.remove();
        closeDeleteBookModal();
        showToast('Book deleted successfully.');
      } else {
        showToast('Failed to delete book.', 'error');
      }
    })
    .catch(() => showToast('Error deleting book.', 'error'));
  });

  // Edit
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      row.querySelectorAll('.static').forEach(el => el.classList.add('d-none'));
      row.querySelectorAll('.edit-input').forEach(el => el.classList.remove('d-none'));
      row.querySelector('.btn-edit').classList.add('d-none');
      row.querySelector('.btn-delete').classList.add('d-none');
      row.querySelector('.btn-save').classList.remove('d-none');
      row.querySelector('.btn-cancel').classList.remove('d-none');
    });
  });

  document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      row.querySelectorAll('.edit-input').forEach(input => {
        const name = input.name;
        const staticText = row.querySelector(`.${name}-cell .static`);
        input.value = staticText.textContent.replace('$', '').trim();
        input.classList.add('d-none');
        staticText.classList.remove('d-none');
      });
      row.querySelector('.btn-edit').classList.remove('d-none');
      row.querySelector('.btn-delete').classList.remove('d-none');
      row.querySelector('.btn-save').classList.add('d-none');
      row.querySelector('.btn-cancel').classList.add('d-none');
    });
  });

  document.querySelectorAll('.btn-save').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      const bookId = row.dataset.bookId;
      const title = row.querySelector('input[name="title"]').value.trim();
      const author = row.querySelector('input[name="author"]').value.trim();
      const category = row.querySelector('input[name="category"]').value.trim();
      const price = row.querySelector('input[name="price"]').value.trim();

      fetch('update_book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${bookId}&title=${encodeURIComponent(title)}&author=${encodeURIComponent(author)}&category=${encodeURIComponent(category)}&price=${encodeURIComponent(price)}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          row.querySelector('.title-cell .static').textContent = title;
          row.querySelector('.author-cell .static').textContent = author;
          row.querySelector('.category-cell .static').textContent = category;
          row.querySelector('.price-cell .static').textContent = `$${parseFloat(price).toFixed(2)}`;

          row.querySelectorAll('.edit-input').forEach(el => el.classList.add('d-none'));
          row.querySelectorAll('.static').forEach(el => el.classList.remove('d-none'));
          row.querySelector('.btn-edit').classList.remove('d-none');
          row.querySelector('.btn-delete').classList.remove('d-none');
          row.querySelector('.btn-save').classList.add('d-none');
          row.querySelector('.btn-cancel').classList.add('d-none');

          showToast('Book updated successfully.');
        } else {
          showToast('Failed to update book.', 'error');
        }
      })
      .catch(() => showToast('Error updating book.', 'error'));
    });
  });
});

function openDeleteBookModal() {
  document.getElementById('deleteBookModal').style.display = 'flex';
}
function closeDeleteBookModal() {
  document.getElementById('deleteBookModal').style.display = 'none';
  bookIdToDelete = null;
  rowToDelete = null;
}
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast-container');
  const messageSpan = document.getElementById('toast-message');
  messageSpan.textContent = message;
  toast.classList.remove('toast-success', 'toast-error');
  toast.classList.add(type === 'error' ? 'toast-error' : 'toast-success');
  toast.style.display = 'block';
  setTimeout(() => { toast.style.display = 'none'; }, 5000);
}
function closeToast() {
  document.getElementById('toast-container').style.display = 'none';
}

// Gắn sự kiện cho nút Export Book
document.getElementById('exportBtn').addEventListener('click', function () {
  document.getElementById('exportFilename').value = '';
  openExportBookModal();
});

document.getElementById('confirmExportBtn').addEventListener('click', function () {
  const filename = document.getElementById('exportFilename').value.trim();
  if (!filename) {
    showToast('Please enter a file name.', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('filename', filename);

  fetch('manual_export.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast('Export successful.');
      window.open(data.download_link, '_blank');
    } else {
      showToast(data.message, 'error');
    }
    closeExportBookModal();
  })
  .catch(() => {
    showToast('Error exporting book.', 'error');
    closeExportBookModal();
  });
});

function openExportBookModal() {
  document.getElementById('exportBookModal').style.display = 'flex';
}

function closeExportBookModal() {
  document.getElementById('exportBookModal').style.display = 'none';
}

</script>
