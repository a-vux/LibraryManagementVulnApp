<?php
include 'init.php';
include 'includes/header.php';

$page = $_GET['page'] ?? 'home';

include $page;

include 'includes/footer.php';
?>
<style>
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

.toast .close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    margin-left: 15px;
    cursor: pointer;
}

</style>
<?php if (isset($_GET['deleted'])): ?>
<div id="toast" class="toast show">
  <div class="toast-content">
    <span>✅ Delete successfully!</span>
    <button onclick="closeToast()" class="close-btn">&times;</button>
  </div>
</div>
<?php endif; ?>
<?php if (isset($_GET['ordered'])): ?>
<div id="toast" class="toast show">
  <div class="toast-content">
    <span>✅ Order placed successfully!</span>
    <button onclick="closeToast()" class="close-btn">&times;</button>
  </div>
</div>
<?php endif; ?>
<script>
function closeToast() {
    const toast = document.getElementById('toast');
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 500);
}

window.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('toast');
    if (toast) {
        setTimeout(() => closeToast(), 5000);
    }
});
</script>
