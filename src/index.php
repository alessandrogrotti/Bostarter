<?php
// Includi il file di connessione
include 'connection.php';

// Ottenere la connessione MySQL
$mysqlConn = getMySQLConnection();

$message = ""; // Messaggio per l'utente

// Gestione dell'invio del form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    
    try {
        $stmt = $mysqlConn->prepare("INSERT INTO users (name) VALUES (:name)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']); // Evita il reinvio del form al refresh
        exit();
    } catch (PDOException $e) {
        $message = "Errore: " . $e->getMessage();
    }
}

// Gestione della rimozione di un utente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    
    try {
        $stmt = $mysqlConn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $message = "Errore nella cancellazione: " . $e->getMessage();
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
  <title>Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">Bostarter</a>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row">
      <div class="col-md-6 mx-auto">
        <div class="card shadow">
          <div class="card-body">
            <h2 class="card-title text-center">Aggiungi Utente</h2>
            <?php if (!empty($message)): ?>
              <div class="alert alert-info"> <?php echo $message; ?> </div>
            <?php endif; ?>
            <form method="post" action="">
              <div class="mb-3">
                <label for="name" class="form-label">Nome:</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-5">
      <div class="col-md-8 mx-auto">
        <div class="card shadow">
          <div class="card-body">
            <h2 class="card-title text-center">Lista degli utenti</h2>
            <ul class="list-group">
              <?php foreach ($users as $user): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <?php echo htmlspecialchars($user['name']); ?>
                  <form method="post" action="" class="d-inline">
                    <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Rimuovi</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="text-center mt-5 p-3 bg-light">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>