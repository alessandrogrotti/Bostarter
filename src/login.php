<?php
include_once 'auth.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (login($email, $password)) {
    header("Location: utente.php");
    exit();
  } else {
    $message = "Credenziali non valide";
  }
}
?>

<?php
include_once 'navbar.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container mt-5">
    <h2>Login</h2>

    <?php if (!empty($message)): ?>
      <p class="text-danger"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="" class="mt-3">
      <div class="mb-3">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary">Accedi</button>
    </form>

    <p class="mt-3">Non hai un account? <a href="registrazione.php">Registrati</a></p>
  </main>

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>
</body>
</html>
