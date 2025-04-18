<?php
session_start();
include 'connection.php';
include 'navbar.php';

$conn = getMySQLConnection();

// 1) Recupera il nome del progetto da GET
$nomeProgetto = isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : '';

// 2) Chiama la SP per ottenere i progetti aperti
try {
    $stmt = $conn->prepare("CALL GetAvailableProjects()");
    $stmt->execute();
    $allProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    die("Errore in CALL GetAvailableProjects(): " . $e->getMessage());
}

// 3) Trova il progetto selezionato
$progetto = null;
foreach ($allProjects as $p) {
    if ($p['Nome'] === $nomeProgetto) {
        $progetto = $p;
        break;
    }
}
if (!$progetto) {
    die("Progetto non trovato o non più aperto.");
}

// 4) Recupera le reward
try {
    $stmtR = $conn->prepare("SELECT Codice, Descrizione, Foto FROM REWARD WHERE Nome_Progetto = :nome");
    $stmtR->bindParam(':nome', $nomeProgetto);
    $stmtR->execute();
    $rewards = $stmtR->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nella query delle reward: " . $e->getMessage());
}

// 5) Se arriva un POST, inserisci il commento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commento'])) {
    $testo     = $_POST['commento'];
    $emailU    = isset($_SESSION['email']) ? $_SESSION['email'] : 'utente@example.com';
    $emailC    = $progetto['Email_Creatore'];

    // Chiama la SP InsertComment
    try {
        $stmtI = $conn->prepare("CALL InsertComment(:p_Testo, :p_Email_Utente, :p_Email_Creatore, :p_Nome_Progetto)");
        $stmtI->bindParam(':p_Testo',           $testo);
        $stmtI->bindParam(':p_Email_Utente',    $emailU);
        $stmtI->bindParam(':p_Email_Creatore',  $emailC);
        $stmtI->bindParam(':p_Nome_Progetto',   $nomeProgetto);
        $stmtI->execute();
        $stmtI->closeCursor();
    } catch (PDOException $e) {
        die("Errore nell'inserimento del commento: " . $e->getMessage());
    }
}

// 6) Recupera tutti i commenti per questo progetto
try {
    $stmtC = $conn->prepare("
        SELECT Data, Testo, Email_Utente 
        FROM COMMENTO 
        WHERE Nome_Progetto = :nome 
        ORDER BY Data DESC
    ");
    $stmtC->bindParam(':nome', $nomeProgetto);
    $stmtC->execute();
    $comments = $stmtC->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore nel recupero dei commenti: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($progetto["Nome"]); ?> | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <?php /* header/navbar */ ?>

  <main class="container mt-5">
    <!-- Info progetto -->
    <section class="mb-5">
      <h2><?php echo htmlspecialchars($progetto["Nome"]); ?></h2>
      <p><?php echo htmlspecialchars($progetto["Descrizione"]); ?></p>
    </section>

    <!-- Reward -->
    <section class="mb-5">
      <h4>Lista delle reward:</h4>
      <?php if ($rewards): ?>
      <ul class="list-group">
        <?php foreach ($rewards as $r): ?>
          <li class="list-group-item">
            <strong><?php echo htmlspecialchars($r['Codice']); ?></strong>:
            <?php echo htmlspecialchars($r['Descrizione']); ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
        <p>Nessuna reward disponibile.</p>
      <?php endif; ?>
    </section>

    <!-- Commenti -->
    <section class="mb-5">
      <h4>Commenti:</h4>
      <!-- Form per nuovo commento -->
      <form method="POST" class="mb-4">
        <div class="mb-3">
          <label for="commento" class="form-label">Inserisci commento:</label>
          <input type="text" id="commento" name="commento" class="form-control" required>
        </div>
        <button class="btn btn-secondary">Aggiungi</button>
      </form>

      <!-- Elenco commenti -->
      <?php if ($comments): ?>
      <ul class="list-group">
        <?php foreach ($comments as $c): ?>
          <li class="list-group-item">
            <small class="text-muted"><?php echo htmlspecialchars($c['Data']); ?> da <?php echo htmlspecialchars($c['Email_Utente']); ?></small>
            <p class="mb-0"><?php echo nl2br(htmlspecialchars($c['Testo'])); ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
        <p>Ancora nessun commento.</p>
      <?php endif; ?>
    </section>

               <!-- Sezione: Profili Richiesti -->
    <section class="mb-5">
      <h4>Profili richiesti:</h4>
      <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>Profilo 1</span>
          <button class="btn btn-outline-primary btn-sm">Invia candidatura</button>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>Profilo 2</span>
          <button class="btn btn-outline-primary btn-sm">Invia candidatura</button>
        </li>
      </ul>
    </section>
  </main>

  <footer class="text-center mt-5 py-3 bg-light">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

  </main>
</body>
</html>
