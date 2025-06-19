<?php
// Bảo vệ nếu không phải admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
  header("Location: index.php?page=home.php");
  exit();
}

$stmt = $pdo->query("SELECT id, username, email FROM users WHERE is_admin = 0 ORDER BY username");
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

.toast-success {
  background-color: #198754; /* Bootstrap green */
}

.toast-error {
  background-color: #dc3545; /* Bootstrap red */
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
          <tr data-user-id="<?= $user['id'] ?>">
            <td><?= $index + 1 ?></td>
            <!-- Username -->
            <td class="username-cell">
              <span class="static"><?= htmlspecialchars($user['username']) ?></span>
              <input class="form-control d-none edit-input" name="username" value="<?= htmlspecialchars($user['username']) ?>">
            </td>
                <!-- Email -->
            <td class="email-cell">
              <span class="static"><?= htmlspecialchars($user['email']) ?></span>
              <input class="form-control d-none edit-input" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </td>
            <td class="action-cell">
              <button class="btn btn-sm btn-primary btn-edit me-2"><i class="bi bi-pencil-fill"></i></button>
              <button class="btn btn-sm btn-delete" onclick="openModal()" data-id="<?= $user['id'] ?>"><i class="bi bi-trash-fill"></i></button>
              <button class="btn btn-sm btn-success btn-save d-none me-2">Save</button>
              <button class="btn btn-sm btn-secondary btn-cancel d-none">Cancel</button>
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

  // Sửa
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      row.querySelectorAll('.static').forEach(el => el.classList.add('d-none'));
      row.querySelectorAll('.edit-input').forEach(el => el.classList.remove('d-none'));

      // Ẩn edit/xóa, hiện save/cancel
      row.querySelector('.btn-edit').classList.add('d-none');
      row.querySelector('.btn-delete').classList.add('d-none');
      row.querySelector('.btn-save').classList.remove('d-none');
      row.querySelector('.btn-cancel').classList.remove('d-none');
    });
  });

  // Cancel
  document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      row.querySelectorAll('.edit-input').forEach(input => {
        const name = input.name;
        const staticText = row.querySelector(`.${name}-cell .static`);
        input.value = staticText.textContent.trim();
        input.classList.add('d-none');
        staticText.classList.remove('d-none');
      });

      row.querySelector('.btn-edit').classList.remove('d-none');
      row.querySelector('.btn-delete').classList.remove('d-none');
      row.querySelector('.btn-save').classList.add('d-none');
      row.querySelector('.btn-cancel').classList.add('d-none');
    });
  });

  // Save
  document.querySelectorAll('.btn-save').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      const userId = row.dataset.userId;
      const username = row.querySelector('input[name="username"]').value.trim();
      const email = row.querySelector('input[name="email"]').value.trim();

      fetch('update_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${userId}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Cập nhật text
          row.querySelector('.username-cell .static').textContent = username;
          row.querySelector('.email-cell .static').textContent = email;

          // Thoát khỏi chế độ chỉnh sửa
          row.querySelectorAll('.edit-input').forEach(el => el.classList.add('d-none'));
          row.querySelectorAll('.static').forEach(el => el.classList.remove('d-none'));
          row.querySelector('.btn-edit').classList.remove('d-none');
          row.querySelector('.btn-delete').classList.remove('d-none');
          row.querySelector('.btn-save').classList.add('d-none');
          row.querySelector('.btn-cancel').classList.add('d-none');

          showToast('User updated successfully.');
        } else {
          showToast('Failed to update user.', 'error');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error updating user.');
      });
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
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast-container');
  const messageSpan = document.getElementById('toast-message');

  messageSpan.textContent = message;

  // Reset class
  toast.classList.remove('toast-success', 'toast-error');
  toast.classList.add(type === 'error' ? 'toast-error' : 'toast-success');

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