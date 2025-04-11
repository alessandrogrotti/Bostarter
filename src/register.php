<?php

ob_start();


include 'connection.php';
include 'navbar.php';

$mysqlConn = getMySQLConnection();

$message = "";


// Controlla se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupera e sanitizza i dati dal form
    $email        = trim($_POST['email']);
    $nickname     = trim($_POST['nickname']);
    $password     = trim($_POST['password']);
    $nome         = trim($_POST['nome']);
    $cognome      = trim($_POST['cognome']);
    $luogoNascita = trim($_POST['luogoNascita']);
    $annoNascita  = trim($_POST['annoNascita']);
    
    // Controlla se sono stati selezionati i ruoli
    $isAdministrator = isset($_POST['administrator']) && $_POST['administrator'] == 1;
    $isCreator       = isset($_POST['creator'])       && $_POST['creator'] == 1;
    
    // Genera l'hash della password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    
    // Controlla se esiste già un utente con la stessa email o lo stesso nickname
    $stmtCheck = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email OR Nickname = :nickname");
    $stmtCheck->execute([
        ':email'    => $email,
        ':nickname' => $nickname
    ]);
    
    if ($stmtCheck->rowCount() > 0) {
        $message = "Email o Nickname già esistente. Utilizza altri dati.";
    } else {
        try {
            // Richiama la stored procedure per la registrazione dell'utente
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
            
            // Se l'utente si registra come Creatore, inserisci anche nella tabella CREATORE
            if ($isCreator) {
                $stmtCreator = $mysqlConn->prepare("INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) VALUES (:email, 0, 0.00)");
                $stmtCreator->execute([':email' => $email]);
            }
            
            // Se l'utente si registra come Amministratore, inserisci anche nella tabella AMMINISTRATORE
            if ($isAdministrator) {
                // Genera un codice di sicurezza (esempio: "SEC" seguito da un numero casuale)
                $codiceSicurezza = "SEC" . rand(1000, 9999);
                $stmtAdmin = $mysqlConn->prepare("INSERT INTO AMMINISTRATORE (Email_Utente, Codice_Sicurezza) VALUES (:email, :codice)");
                $stmtAdmin->execute([
                    ':email'  => $email,
                    ':codice' => $codiceSicurezza
                ]);
            }
            
            // Registrazione avvenuta con successo: esegui il redirect
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $message = "Errore nella registrazione: " . $e->getMessage();
        }
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
