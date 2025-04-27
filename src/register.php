<?php
include_once 'connection.php';

$mysqlConn = getMySQLConnection();

$message = "";
$codiceSicurezzaChiaro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email        = trim($_POST['email']);
  $nickname     = trim($_POST['nickname']);
  $password     = trim($_POST['password']);
  $nome         = trim($_POST['nome']);
  $cognome      = trim($_POST['cognome']);
  $luogoNascita = trim($_POST['luogoNascita']);
  $annoNascita  = trim($_POST['annoNascita']);

  $isAdministrator = isset($_POST['administrator']) && $_POST['administrator'] == 1;
  $isCreator       = isset($_POST['creator'])       && $_POST['creator'] == 1;

  $passwordHash = md5($password);

  $stmtCheck = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email OR Nickname = :nickname");
  $stmtCheck->execute([
    ':email'    => $email,
    ':nickname' => $nickname
  ]);

  if ($stmtCheck->rowCount() > 0) {
    $message = "Email o Nickname già esistente. Utilizza altri dati.";
  } else {
    try {
      $stmtRegister = $mysqlConn->prepare("CALL RegisterUser(:email, :nickname, :password, :luogo, :anno, :nome, :cognome)");
      $stmtRegister->execute([
        ':email'    => $email,
        ':nickname' => $nickname,
        ':password' => $passwordHash,
        ':luogo'    => $luogoNascita,
        ':anno'     => $annoNascita,
        ':nome'     => $nome,
        ':cognome'  => $cognome
      ]);

      if ($isCreator) {
        $stmtCreator = $mysqlConn->prepare("INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) VALUES (:email, 0, 0.00)");
        $stmtCreator->execute([':email' => $email]);
      }

      if ($isAdministrator) {
        $codiceSicurezzaChiaro = isset($_POST['codiceSicurezza']) ? trim($_POST['codiceSicurezza']) : "SEC" . rand(1000, 9999);
        $codiceSicurezzaHash = md5($codiceSicurezzaChiaro);

        $stmtAdmin = $mysqlConn->prepare("INSERT INTO AMMINISTRATORE (Email_Utente, Codice_Sicurezza) VALUES (:email, :codice)");
        $stmtAdmin->execute([
          ':email'  => $email,
          ':codice' => $codiceSicurezzaHash
        ]);
      }

      if (!$isAdministrator) {
        header("Location: login.php");
        exit();
      }
    } catch (PDOException $e) {
      $message = "Errore nella registrazione: " . $e->getMessage();
    }
  }
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrazione</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- Hero Section -->
<header class="hero">
  <div class="container text-center py-5">
    <h1 class="display-5 text-white mb-3 animate-fadein">Registrazione Utente</h1>
    <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Crea il tuo account per entrare a far parte di Bostarter</p>
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

      <?php if (!empty($codiceSicurezzaChiaro)): ?>
        <div class="alert alert-success animate-fadein" role="alert" style="animation-delay: 0.4s;">
          Registrazione completata! Il tuo <strong>codice di sicurezza</strong> come Amministratore è:<br>
          <code><?php echo $codiceSicurezzaChiaro; ?></code><br>
          <em>Conservalo con attenzione, non potrai più recuperarlo.</em>
        </div>
        <a href="login.php" class="btn btn-primary w-100">Vai al login</a>
      <?php else: ?>

      <div class="card shadow-sm animate-fadein" style="animation-delay: 0.4s;">
        <div class="card-body p-5">
          <h2 class="mb-4 text-center">Registrati</h2>
          <form method="POST" action="">

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
              <label for="nickname" class="form-label">Nickname</label>
              <input type="text" class="form-control" name="nickname" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>

            <div class="mb-3">
              <label for="nome" class="form-label">Nome</label>
              <input type="text" class="form-control" name="nome" required>
            </div>

            <div class="mb-3">
              <label for="cognome" class="form-label">Cognome</label>
              <input type="text" class="form-control" name="cognome" required>
            </div>

            <div class="mb-3">
              <label for="luogoNascita" class="form-label">Luogo di Nascita</label>
              <input type="text" class="form-control" name="luogoNascita" required>
            </div>

            <div class="mb-3">
              <label for="annoNascita" class="form-label">Anno di Nascita</label>
              <input type="number" class="form-control" name="annoNascita" required>
            </div>

            <div class="mb-3">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="administrator" value="1">
                <label class="form-check-label" for="administrator">Amministratore</label>
              </div>
            </div>

            <?php if (isset($_POST['administrator']) && $_POST['administrator'] == 1): ?>
              <div class="mb-3">
                <label for="codiceSicurezza" class="form-label">Codice di Sicurezza</label>
                <input type="text" class="form-control" name="codiceSicurezza" required>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="creator" value="1">
                <label class="form-check-label" for="creator">Creatore</label>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrati</button>
          </form>
        </div>
      </div>

      <?php endif; ?>

    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
