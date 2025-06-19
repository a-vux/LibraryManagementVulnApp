<?php
// Bảo vệ nếu không phải admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
  header("Location: index.php?page=home.php");
  exit();
}

$stmt = $pdo->query("SELECT id, username, email FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
/* for modal */
/* Modal overlay */
.modal-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0,0,0,0.5);
  z-index: 9998;
  justify-content: center;
  align-items: center;
}

/* Modal content */
.modal-content {
  background-color: white;
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  position: relative;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  animation: slideIn 0.3s ease-out;
}

/* Animation */
@keyframes slideIn {
  from {
    transform: translateY(-40px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Close button (X) */
.close-modal {
  position: absolute;
  top: 8px;
  right: 12px;
  background: none;
  border: none;
  font-size: 24px;
  color: #888;
  cursor: pointer;
}

/* Modal text */
.modal-text {
  font-size: 18px;
  margin-bottom: 20px;
  font-weight: 500;
}

/* Buttons inside modal */
.modal-buttons .btn {
  margin: 0 10px;
}
</style>
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">User Lists</h3>
    <a href="#" class="btn btn-primary">Add New User</a> 
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-success text-uppercase">
        <tr>
          <th scope="col">No</th>
          <th scope="col">Username</th>
          <th scope="col">Email</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $index => $user): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <a href="index.php?page=edit_user.php&id=<?= $user['id'] ?>" class="btn btn-sm me-2" title="Edit">
                <i class="bi bi-pencil-fill"></i>
              </a>
              <button class="btn btn-danger" onclick="openModal()" data-id="<?= $user['id'] ?>"><i class="bi bi-trash-fill"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
          <tr><td colspan="4" class="text-center">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <button class="close-modal" onclick="closeModal()">&times;</button>
    <p class="modal-text">Are you sure you want to delete this account?</p>
    <div class="modal-buttons">
      <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes</button>
      <button type="button" class="btn btn-secondary" onclick="closeModal()">No</button>
    </div>
  </div>
</div>
<!-- Toast Notification -->
<div id="toast-container" style="
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  display: none;
  min-width: 250px;
  padding: 15px 20px;
  background-color: #198754;
  color: white;
  border-radius: 6px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  animation: fadein 0.5s, fadeout 0.5s 4.5s;
">
  <span id="toast-message">User deleted successfully.</span>
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
</style>

<script>
let userIdToDelete = null;
let rowToDelete = null;

document.addEventListener('DOMContentLoaded', function () {
  // Bắt sự kiện khi bấm nút trash
  document.querySelectorAll('button[data-id]').forEach(btn => {
    btn.addEventListener('click', function () {
      userIdToDelete = this.dataset.id;
      rowToDelete = this.closest('tr');
      openModal();
    });
  });

  // Khi nhấn nút Yes trong modal
  document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!userIdToDelete) return;

    fetch('delete_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `user_id=${encodeURIComponent(userIdToDelete)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (rowToDelete) rowToDelete.remove();
        closeModal();
        showToast('User deleted successfully.');
      } else {
        alert(data.message || 'Failed to delete user.');
      }
    })
    .catch(err => {
      console.error('Error:', err);
      alert('Error deleting user.');
    });
  });
});
// cho modal
function openModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
    userIdToDelete = null;
    rowToDelete = null;
}
// cho toast
function showToast(message) {
  const toast = document.getElementById('toast-container');
  document.getElementById('toast-message').textContent = message;
  toast.style.display = 'block';

  // Tự động ẩn sau 5s
  setTimeout(() => {
    toast.style.display = 'none';
  }, 5000);
}

function closeToast() {
  document.getElementById('toast-container').style.display = 'none';
}

</script>