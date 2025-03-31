<?php
// Includi il file di connessione
include 'connection.php';

// Ottenere la connessione MySQL
$mysqlConn = getMySQLConnection();

// Ottenere la connessione MongoDB
$mongoDb = getMongoDBConnection();

// Gestione dell'invio del form
$message = ""; // Messaggio per l'utente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    
    try {
        $stmt = $mysqlConn->prepare("INSERT INTO users (name) VALUES (:name)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $message = "Utente aggiunto con successo!";
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
    }
}

// Recupero degli utenti
$users = [];
try {
    $stmt = $mysqlConn->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Errore nel recupero utenti: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test WIS</title>
</head>
<body>
  <header>
    <h1>Benvenuto nel sistema WIS</h1>
  </header>

  <main>
    <section>
      <h2>Form di aggiunta utente</h2>
      <?php if (!empty($message)): ?>
        <p><?php echo $message; ?></p>
      <?php endif; ?>
      <form method="post" action="">
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" required>
        <button type="submit">Aggiungi</button>
      </form>
    </section>

    <section>
      <h2>Lista degli utenti</h2>
      <ul id="usersList">
        <?php foreach ($users as $user): ?>
          <li><?php echo htmlspecialchars($user['name']); ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  </main>

  <footer>
    <p>Progetto WIS &copy; 2025</p>
  </footer>
</body>
</html>
