<?php require_once 'init.php';
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]) ?? '';
    $password = trim($_POST["password"]) ?? '';
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
      // print email and password for debugging
  
      // su dung prepared statements to prevent SQL injection
      // $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
      // $stmt->execute([$email]);
      // $user = $stmt->fetch();
  
      // ghep chuoi ma khong validate input
      $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
      $user = $pdo->query($query)->fetch(PDO::FETCH_ASSOC); 
      
      if ($user) {
          $_SESSION["user"] = [
              "id" => $user["id"],
              "username" => $user["username"],
              "email" => $user["email"],
              "is_admin" => $user["is_admin"]
          ];

          $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
          $stmt->execute([$user['id']]);
          $cart = $stmt->fetch();

          if (!$cart) {
              $insertCart = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
              $insertCart->execute([$user['id']]);
          }

          header("Location: index.php?page=home.php");
          exit();
      } else {
          $error = "Invalid credentials.";
      }
    }
}
?>
<?php include 'includes/header.php'; ?>
  <div class="container mt-5">
    <h2 class="mb-4">Login</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="mb-3">
        <label>Email address *</label>
        <input type="text" name="email" class="form-control" required />
      </div>
      <div class="mb-3">
        <label>Password *</label>
        <input type="password" name="password" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-dark w-100">Login</button>
    </form>
  </div>
<?php include 'includes/footer.php'; ?>
