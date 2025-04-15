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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // 🔎 Recupera l'utente dal database
        $stmt = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se l'utente esiste e la password è corretta
        if ($user) {
            if (password_verify($password, $user['Password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nickname'] = $user['Nickname'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['is_creator'] = $user['is_creator'];

                writeLog('Login', "Utente {$user['Nickname']} ($email) ha effettuato l'accesso");
                header("Location: index.php");
                exit();
            } else {
                writeLog('Login fallito', "Tentativo di login fallito con email: $email");
                $message = "Email o password errati.";
            }
        } else {
            $message = "Email o password errati.";
        }
    } catch (PDOException $e) {
        $message = "Errore durante il login: " . $e->getMessage();
    }
}
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

  <main>
    <h2>Login</h2>
    <?php if (!empty($message)): ?>
      <p style="color:red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <p>Email:</p>
      <input type="email" name="email" required>

      <p>Password:</p>
      <input type="password" name="password" required>

      <button type="submit">Accedi</button>
    </form>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
