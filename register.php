<?php
require_once 'init.php';
require_once 'config/config.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    // $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $error = "Email is already registered.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $password])) {
            $success = "Account created successfully.";
        } else {
            $error = "Error registering user.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
  <div class="container mt-5">
    <h2 class="mb-4">Register</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="mb-3">
        <label>Username *</label>
        <input type="text" name="username" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Email address *</label>
        <input type="email" name="email" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Password *</label>
        <input type="password" name="password" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-dark w-100">Register</button>
    </form>
  </div>
<?php include 'includes/footer.php'; ?>
