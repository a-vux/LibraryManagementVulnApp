<?php

$user_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<div class='container mt-5'><h2>User not found.</h2></div>";
    return;
}
?>
<style>
    #quantityInput {
        max-width: 100px;
        text-align: center;
        font-size: 18px;
        -moz-appearance: textfield;
    }
    .input-group .btn {
        width: 60px;
    }
    .profile-box {
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
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
<!-- Toast update thanh cong -->
<?php if (isset($_GET['updated'])): ?>
<div id="toast" class="toast show">
  <div class="toast-content">
    <span>✅ Update successfully</span>
    <button onclick="closeToast()" class="close-btn">&times;</button>
  </div>
</div>
<?php endif; ?>
<!-- Toast bao trung email -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'email_exists'): ?>
<div id="error-toast" class="toast error show">
  <div class="toast-content">
    <span>❌ Email already exists</span>
    <button onclick="closeErrorToast()" class="close-btn">&times;</button>
  </div>
</div>
<?php endif; ?>

<div class="container mt-5">
    <div class="profile-box">
        <h3 class="text-primary">User Profile</h3>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

        <hr>
        <h5 class="text-warning"><strong style="font-weight: 700">Edit Profile</strong></h5>
        <form method="post" action="update_profile.php" class="mb-3">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <div class="mb-2">
                <label>Username:</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-2">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>

        <hr>
        <h5 class="text-danger">Delete Account</h5>
        <button class="btn btn-danger" onclick="openModal()">Delete Account</button>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-overlay">
  <div class="modal-content">
    <button class="close-modal" onclick="closeModal()">&times;</button>
    <p class="modal-text">Are you sure to delete this account?</p>
    <div class="modal-buttons">
      <form id="deleteForm" method="post" action="delete_account.php">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <button type="submit" class="btn btn-danger">Yes</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">No</button>
      </form>
    </div>
  </div>
</div>

<script>
// cho update toast
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
// cho trung mail toast
function closeErrorToast() {
    const toast = document.getElementById('error-toast');
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 500);
}

window.addEventListener('DOMContentLoaded', () => {
    const errorToast = document.getElementById('error-toast');
    if (errorToast) {
        setTimeout(() => closeErrorToast(), 5000);
    }
});
// cho modal
function openModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

</script>

