<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    logout();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Test Navbar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold text-primary" href="index.php" style="font-family: 'Libre Baskerville', serif;">Bostarter</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarContent">
        <div class="ms-auto d-flex align-items-center gap-3">

          <a href="visualizzaProgetti.php" class="btn btn-link text-dark fw-medium">Esplora Progetti</a>

          <?php if (!isLoggedIn()): ?>
            <a href="login.php" class="btn btn-outline-secondary">Accedi</a>
            <a href="register.php" class="btn btn-primary">Registrati</a>
          <?php else: ?>
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Aree Riservate
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="utente.php">Utente</a></li>
                <li><a class="dropdown-item" href="loginAdmin.php">Admin</a></li>
                <li><a class="dropdown-item" href="indexCreatore.php">Creatore</a></li>
              </ul>
            </div>

            <form method="POST" action="" class="d-inline">
              <button type="submit" name="logout" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Esci
              </button>
            </form>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </nav>

<!-- Bootstrap Bundle (con Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
