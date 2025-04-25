<?php
include_once 'connection.php';

$mysqlConn = getMySQLConnection();

$message = "";
$codiceSicurezzaChiaro = ""; // Variabile per mostrare il codice di sicurezza in chiaro (se generato)

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
        // Usa il codice inserito dall'utente o ne genera uno random
        $codiceSicurezzaChiaro = isset($_POST['codiceSicurezza']) ? trim($_POST['codiceSicurezza']) : "SEC" . rand(1000, 9999);
        $codiceSicurezzaHash = md5($codiceSicurezzaChiaro);

        $stmtAdmin = $mysqlConn->prepare("INSERT INTO AMMINISTRATORE (Email_Utente, Codice_Sicurezza) VALUES (:email, :codice)");
        $stmtAdmin->execute([
          ':email'  => $email,
          ':codice' => $codiceSicurezzaHash
        ]);
      }

      // Solo redirect se non serve mostrare il codice di sicurezza
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
  <link rel="stylesheet" href="style.css"> 
</head>
<body>

  <main>
    <h2>Registrazione</h2>

    <?php if (!empty($message)): ?>
      <p style="color:red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (!empty($codiceSicurezzaChiaro)): ?>
      <p style="color:green;">
        Registrazione completata! Il tuo <strong>codice di sicurezza</strong> come Amministratore è:<br>
        <code><?php echo $codiceSicurezzaChiaro; ?></code><br>
        <em>Conservalo con attenzione, non potrai più recuperarlo.</em>
      </p>
      <a href="login.php">Vai al login</a>
    <?php else: ?>
    
    <form method="POST" action="">
      <p>Email:</p>
      <input type="email" name="email" required>

      <p>Nickname:</p>
      <input type="text" name="nickname" required>

      <p>Password:</p>
      <input type="password" name="password" required>

      <p>Nome:</p>
      <input type="text" name="nome" required>

      <p>Cognome:</p>
      <input type="text" name="cognome" required>

      <p>Luogo di nascita:</p>
      <input type="text" name="luogoNascita" required>

      <p>Anno di nascita:</p>
      <input type="number" name="annoNascita" required>

      <p>Ruolo:</p>
      <label><input type="checkbox" name="administrator" value="1" <?php echo isset($_POST['administrator']) ? 'checked' : ''; ?>> Amministratore</label><br>

      <?php if (isset($_POST['administrator']) && $_POST['administrator'] == 1): ?>
        <p>Inserisci un codice di sicurezza:</p>
        <input type="text" name="codiceSicurezza" required><br>
      <?php endif; ?>

      <label><input type="checkbox" name="creator" value="1" <?php echo isset($_POST['creator']) ? 'checked' : ''; ?>> Creatore</label><br><br>

      <button type="submit">Registrati</button>
    </form>

    <?php endif; ?>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
