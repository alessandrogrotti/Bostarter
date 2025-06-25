<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    logout();
    header("Location: index.php");
    exit();
}

$isLoggedIn = isLoggedIn();
$isAdmin = isAdmin();
$isCreator = isCreator();
$errorMessage = null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="index.php" style="font-family: 'Libre Baskerville', serif;">
      Bostarter
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <div class="ms-auto d-flex align-items-center gap-3">

        <a href="visualizzaProgetti.php" class="btn btn-link text-dark fw-medium">
          Esplora Progetti
        </a>

        <?php if (!$isLoggedIn): ?>
          <a href="login.php" class="btn btn-outline-secondary">
            Accedi
          </a>
          <a href="register.php" class="btn btn-primary">
            Registrati
          </a>
        <?php else: ?>
          <?php if ($errorMessage): ?>
            <div class="alert alert-danger mt-3" role="alert">
              <?= htmlspecialchars($errorMessage) ?>
            </div>
          <?php endif; ?>

          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              Aree Riservate
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="utente.php">Utente</a></li>
              <?php if ($isAdmin): ?>
                <li><a class="dropdown-item" href="loginAdmin.php">Admin</a></li>
              <?php endif; ?>
              <?php if ($isCreator): ?>
                <li><a class="dropdown-item" href="indexCreatore.php">Creatore</a></li>
              <?php endif; ?>
            </ul>
          </div>

          <form method="POST" action="" class="align-self-center mt-3">
            <button type="submit" name="logout" class="btn btn-outline-danger">
              <i class="bi bi-box-arrow-right"></i> Esci
            </button>
          </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>