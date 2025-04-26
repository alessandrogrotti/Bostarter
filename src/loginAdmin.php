<?php
include_once 'auth.php';

// Se il form viene inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $code = $_POST['password']; // Codice di sicurezza inserito dall'utente

  // Verifica il codice di sicurezza
  if (verifyAdminCode($code)) {
    // Se il codice è corretto, aggiorna la sessione e reindirizza
    $_SESSION['is_admin_verified'] = true;
    header("Location: amministratore.php");
    exit;
  } else {
    $message = "Codice di sicurezza errato.";
  }
}
?>
<?php include_once 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin | Bostarter</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- Hero Section (più semplice per login) -->
<header class="hero">
  <div class="container text-center py-5">
    <h1 class="display-5 text-white mb-3 animate-fadein">Accesso Amministratore</h1>
    <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Inserisci il codice di sicurezza per entrare</p>
  </div>
</header>

<main class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">

      <?php if (!empty($message)): ?>
        <div class="alert alert-danger animate-fadein" role="alert" style="animation-delay: 0.4s;">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="card shadow-sm animate-fadein" style="animation-delay: 0.4s;">
        <div class="card-body p-5">
          <h2 class="mb-4 text-center">Login Admin</h2>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="password" class="form-label">Codice di Sicurezza</label>
              <input type="password" class="form-control" id="password" name="password" required autofocus>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Accedi</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</main>

<?php include_once 'footer.php'; ?>
</body>
</html>
