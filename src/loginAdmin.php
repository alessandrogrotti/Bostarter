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

include 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Admin</title>
  <link rel="stylesheet" href="style.css"> 
</head>
<body>

  <main>
    <h2>Login admin</h2>
    <?php if (!empty($message)): ?>
      <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <p>Codice di sicurezza:</p>
      <input type="password" name="password" required>

      <button type="submit">Accedi</button>
    </form>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
