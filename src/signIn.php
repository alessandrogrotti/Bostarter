<?php
// Includi il file di connessione
include 'connection.php';
include 'navbar.php';

$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();
$message = "";

// Funzione per scrivere un log
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

// Gestione del form di registrazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $nickname = $_POST['nickname'];
    $password = $_POST['password'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $luogo = $_POST['luogoNascita'];
    $anno = $_POST['annoNascita'];

    $isAdmin = isset($_POST['administrator']) ? 1 : 0;
    $isCreator = isset($_POST['creator']) ? 1 : 0;

    try {
        $stmt = $mysqlConn->prepare("INSERT INTO users (email, nickname, password, nome, cognome, luogo_nascita, anno_nascita, is_admin, is_creator)
                                     VALUES (:email, :nickname, :password, :nome, :cognome, :luogo, :anno, :admin, :creator)");
        $stmt->execute([
            ':email' => $email,
            ':nickname' => $nickname,
            ':password' => $password, // In produzione: usare hash
            ':nome' => $nome,
            ':cognome' => $cognome,
            ':luogo' => $luogo,
            ':anno' => $anno,
            ':admin' => $isAdmin,
            ':creator' => $isCreator
        ]);

        writeLog('Registrazione', "Nuovo utente: $nickname ($email)");

        header("Location: login.html");
        exit();
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
  <title>Sign In</title>
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

      <button type="submit">Sign In</button>
    </form>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
