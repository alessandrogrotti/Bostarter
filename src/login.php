<?php
include_once 'auth.php';
include_once 'mongodb.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    writeLog('Tentativo di login', ['email' => $email]);

    if (login($email, $password)) {
        writeLog('Login riuscito', ['email' => $email]);
        header("Location: utente.php");
        exit();
    } else {
        writeLog('Login fallito', ['email' => $email]);
        $message = "Credenziali non valide";
    }
}
?>

<?php include_once 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="hero">
  <div class="container text-center py-5">
    <h1 class="display-5 text-white mb-3 animate-fadein">Login Utente</h1>
    <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Inserisci le tue credenziali per entrare</p>
  </div>
</header>

<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">

      <?php if (!empty($message)): ?>
        <div class="alert alert-danger animate-fadein" role="alert" style="animation-delay: 0.4s;">
          <?= htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="card animate-fadein" style="animation-delay: 0.4s;">
        <div class="card-body p-5">
          <h2 class="mb-4 text-center">Login</h2>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Accedi</button>
            </div>
          </form>
        </div>
      </div>

      <p class="mt-3 text-center">Non hai un account? <a href="register.php">Registrati</a></p>

    </div>
  </div>
</main>

<?php include_once 'footer.php'; ?>

</body>
</html>
