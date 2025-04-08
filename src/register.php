<?php
include 'connection.php';
include 'navbar.php';

$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();
$message = "";

function writeLog($action, $details) {
    global $logCollection;

    $logEntry = [
        'action' => $action,
        'details' => $details,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
    ];

    $bulkWrite = new MongoDB\Driver\BulkWrite;
    $bulkWrite->insert($logEntry);

    try {
        $logCollection->executeBulkWrite('Bostarter.logs', $bulkWrite);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Errore durante la scrittura del log: " . $e->getMessage());
    }
}

// Codice per la registrazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $nickname = $_POST['nickname'];
  $password = $_POST['password']; // Password in chiaro
  $nome = $_POST['nome'];
  $cognome = $_POST['cognome'];
  $luogo = $_POST['luogoNascita'];
  $anno = $_POST['annoNascita'];

  // Crea l'hash della password
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $isAdmin = isset($_POST['administrator']);
  $isCreator = isset($_POST['creator']);

  try {
      // 🔎 Controllo se email o nickname esistono già
      $checkStmt = $mysqlConn->prepare("SELECT COUNT(*) FROM UTENTE WHERE Email = :email OR Nickname = :nickname");
      $checkStmt->execute([':email' => $email, ':nickname' => $nickname]);
      $exists = $checkStmt->fetchColumn();

      if ($exists > 0) {
          $message = "Errore: esiste già un utente con questa email o nickname.";
      } else {
          // 1. Inserimento nella tabella UTENTE con la password hashata
          $stmt = $mysqlConn->prepare("INSERT INTO UTENTE (Email, Nickname, Password, Nome, Cognome, Luogo, Anno) 
                                      VALUES (:email, :nickname, :password, :nome, :cognome, :luogo, :anno)");
          $stmt->execute([
              ':email' => $email,
              ':nickname' => $nickname,
              ':password' => $hashedPassword, // Usa la password hashata
              ':nome' => $nome,
              ':cognome' => $cognome,
              ':luogo' => $luogo,
              ':anno' => $anno
          ]);

          // 2. Inserimento nella tabella CREATORE se selezionato
          if ($isCreator) {
              $stmtCreator = $mysqlConn->prepare("INSERT INTO CREATORE (Email_Utente) VALUES (:email)");
              $stmtCreator->execute([':email' => $email]);
          }

          // 3. Inserimento nella tabella AMMINISTRATORE se selezionato
          if ($isAdmin) {
              $codiceSicurezza = bin2hex(random_bytes(8));
              $stmtAdmin = $mysqlConn->prepare("INSERT INTO AMMINISTRATORE (Email_Utente, Codice_Sicurezza) VALUES (:email, :codice)");
              $stmtAdmin->execute([':email' => $email, ':codice' => $codiceSicurezza]);
          }

          writeLog('Registrazione', "Nuovo utente: $nickname ($email)");
          header("Location: login.html");
          exit();
      }

  } catch (PDOException $e) {
      $message = "Errore nella registrazione: " . $e->getMessage();
  }
}
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
      <label><input type="checkbox" name="administrator" value="1"> Amministratore</label><br>
      <label><input type="checkbox" name="creator" value="1"> Creatore</label><br><br>

      <button type="submit">Registrati</button>
    </form>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
