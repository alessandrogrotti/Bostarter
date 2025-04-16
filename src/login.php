<?php
session_start(); 

include_once 'auth.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        header("Location: index.php");
        exit();
    } else {
        $message = "Email o password errati.";
    }
}
?>

<?php include 'navbar.php'; ?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="style.css"> 
</head>
<body>

  <main>
    <h2>Login</h2>
    <?php if (!empty($message)): ?>
      <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <p>Email:</p>
      <input type="email" name="email" required>

      <p>Password:</p>
      <input type="password" name="password" required>

      <button type="submit">Accedi</button>
    </form>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
