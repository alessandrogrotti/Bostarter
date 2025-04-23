<?php
// visualizza_progetto.php
session_start();

// include per auth, DB e logging
include_once 'connection.php';
include_once 'navbar.php';
include_once 'mongodb.php';
include_once 'auth.php';

$conn          = getMySQLConnection();
$logCollection = getMongoDBConnection();

// 1) Recupera il nome del progetto da GET
$nomeProgetto = isset($_GET['nome']) ? trim($_GET['nome']) : '';
if ($nomeProgetto === '') {
    die("Nome progetto non specificato.");
}

// 2) Richiama la SP per ottenere i progetti aperti
try {
    $stmt = $conn->prepare("CALL GetAvailableProjects()");
    $stmt->execute();
    $allProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    die("Errore in GetAvailableProjects(): " . $e->getMessage());
}

// 3) Trova il progetto selezionato
$progetto = null;
foreach ($allProjects as $p) {
    if ($p['Nome'] === $nomeProgetto) {
        $progetto = $p;
        break;
    }
}
if (! $progetto) {
    die("Progetto non trovato o non più aperto.");
}

// 4) Recupera le reward
try {
    $stmtR = $conn->prepare("
        SELECT Codice, Descrizione, Foto 
        FROM REWARD 
        WHERE Nome_Progetto = :nome
    ");
    $stmtR->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtR->execute();
    $rewards = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    $stmtR->closeCursor();
} catch (PDOException $e) {
    die("Errore nella query delle reward: " . $e->getMessage());
}

// 5) Se arriva un POST con commento, inseriscilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commento'])) {
    // richiedo login per inserire
    requireLogin();

    $testoCommento = trim($_POST['commento']);
    $emailUtente   = $_SESSION['id'];
    $emailCreatore = $progetto['Email_Creatore'];

    try {
        $stmtI = $conn->prepare("
            CALL InsertComment(
                :p_Testo,
                :p_Email_Utente,
                :p_Email_Creatore,
                :p_Nome_Progetto
            )
        ");
        $stmtI->bindParam(':p_Testo',          $testoCommento, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Utente',   $emailUtente,   PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Creatore', $emailCreatore, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Nome_Progetto',  $nomeProgetto,  PDO::PARAM_STR);
        $stmtI->execute();
        $stmtI->closeCursor();

        // Log su MongoDB
        writeLog(
            'Inserimento commento',
            "Utente $emailUtente ha commentato progetto $nomeProgetto"
        );
        // dopo inserimento, redirect per evitare doppio invio
        header("Location: visualizza_progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
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
    $stmtC->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtC->execute();
    $comments = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    $stmtC->closeCursor();
} catch (PDOException $e) {
    die("Errore nel recupero dei commenti: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($progetto["Nome"]) ?> | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<<<<<<< Updated upstream
  <link rel="stylesheet" href="../css/style.css">
=======
  <link rel="stylesheet" href="style.css">
>>>>>>> Stashed changes
</head>
<body>
  <?php /* navbar già inclusa */ ?>

  <main class="container mt-5">
    <!-- Informazioni progetto -->
    <section class="mb-5">
      <h2><?= htmlspecialchars($progetto["Nome"]) ?></h2>
      <p><?= nl2br(htmlspecialchars($progetto["Descrizione"])) ?></p>
    </section>

    <!-- Reward -->
    <section class="mb-5">
      <h4>Lista delle reward:</h4>
      <?php if ($rewards): ?>
        <ul class="list-group">
          <?php foreach ($rewards as $r): ?>
            <li class="list-group-item">
              <strong><?= htmlspecialchars($r['Codice']) ?></strong>:
              <?= htmlspecialchars($r['Descrizione']) ?>
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

      <?php if (isLoggedIn()): ?>
        <!-- Form per nuovo commento -->
        <form method="POST" class="mb-4">
          <div class="mb-3">
            <label for="commento" class="form-label">Inserisci commento:</label>
            <input
              type="text"
              id="commento"
              name="commento"
              class="form-control"
              required
              maxlength="1000"
            >
          </div>
          <button type="submit" class="btn btn-secondary">Aggiungi</button>
        </form>
      <?php else: ?>
        <p>
          <a href="login.php">Accedi</a> per inserire un commento.
        </p>
      <?php endif; ?>

      <!-- Elenco commenti -->
      <?php if ($comments): ?>
        <ul class="list-group">
          <?php foreach ($comments as $c): ?>
            <li class="list-group-item">
              <small class="text-muted">
                <?= htmlspecialchars($c['Data']) ?> da <?= htmlspecialchars($c['Email_Utente']) ?>
              </small>
              <p class="mb-0"><?= nl2br(htmlspecialchars($c['Testo'])) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Ancora nessun commento.</p>
      <?php endif; ?>
    </section>

    <!-- Profili richiesti -->
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
    <p>Progetto Bostarter &copy; <?= date('Y') ?></p>
  </footer>
</body>
</html>
