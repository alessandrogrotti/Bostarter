<?php
// 1) Include tutte le librerie PRIMA di qualsiasi logica POST o output
include_once 'connection.php';       // definisce getMySQLConnection()
include_once 'mongodb.php';         // definisce getMongoDBConnection() e writeLog()
include_once 'auth.php';            // definisce requireLogin(), isLoggedIn()

$conn          = getMySQLConnection();
$logCollection = getMongoDBConnection();

// 2) Recupera il nome del progetto da GET
$nomeProgetto = isset($_GET['nome']) ? trim($_GET['nome']) : '';
if ($nomeProgetto === '') {
    die("Nome progetto non specificato.");
}

// 3) Gestione invio commento (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commento'])) {
    // a) Verifica autenticazione
    requireLogin();

    // b) Pulisci il commento
    $testoCommento = trim($_POST['commento']);
    $emailUtente   = $_SESSION['id'];

    // c) Recupera l'email del creatore per la SP
    $stmtP = $conn->prepare(
        "SELECT Email_Creatore
         FROM PROGETTO
         WHERE Nome = :nome"
    );
    $stmtP->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtP->execute();
    $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
    $stmtP->closeCursor();

    if (! $rowP) {
        die("Progetto non trovato.");
    }
    $emailCreatore = $rowP['Email_Creatore'];

    // d) Chiamata alla stored procedure per inserire il commento
    try {
        $stmtI = $conn->prepare(
            "CALL InsertComment(
                :p_Testo,
                :p_Email_Utente,
                :p_Email_Creatore,
                :p_Nome_Progetto
            )"
        );
        $stmtI->bindParam(':p_Testo',          $testoCommento, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Utente',   $emailUtente,   PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Creatore', $emailCreatore, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Nome_Progetto',  $nomeProgetto,  PDO::PARAM_STR);
        $stmtI->execute();
        $stmtI->closeCursor();

        // e) Log su MongoDB
        writeLog(
            'Inserimento commento',
            "Utente $emailUtente ha commentato progetto $nomeProgetto"
        );

        // f) Redirect PRIMA di qualsiasi output
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
    } catch (PDOException $e) {
        die("Errore nell'inserimento del commento: " . $e->getMessage());
    }
}

// 5) Recupera i dettagli del progetto
try {
  $stmt = $conn->prepare(
      "SELECT Nome, Descrizione, Email_Creatore
       FROM PROGETTO
       WHERE Nome = :nome AND Stato = 'aperto'"
  );
  $stmt->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
  $stmt->execute();
  $progetto = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt->closeCursor();
} catch (PDOException $e) {
  die("Errore nel recupero del progetto: " . $e->getMessage());
}
if (! $progetto) {
  die("Progetto non trovato o non più aperto.");
}

// ——— 6) GESTIONE INVIO NUOVO PROFILO + SKILL RICHIESTA
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['profilo'], $_POST['skill'], $_POST['level'])
) {
    // a) Assicurati che l’utente sia loggato
    requireLogin();

    // b) Leggi e sanifica i dati
    $nomeProfilo   = trim($_POST['profilo']);
    $competenza    = $_POST['skill'];
    $livello       = (int) $_POST['level'];
    // Ora $progetto è DEFINITO perché l'abbiamo caricato sopra
    $nomeProgetto  = $progetto['Nome'];
    $emailCreatore = $progetto['Email_Creatore'];

    try {
        // c) Chiamo la stored procedure InserisciProfilo
        $stmtSP = $conn->prepare("
            CALL InserisciProfilo(
                :nomeProfilo,
                :nomeProgetto,
                :emailCreatore
            )
        ");
        $stmtSP->execute([
            ':nomeProfilo'   => $nomeProfilo,
            ':nomeProgetto'  => $nomeProgetto,
            ':emailCreatore' => $emailCreatore
        ]);
        $stmtSP->closeCursor();

        // d) Prendo l'ID dell'ultimo insert
        $idProfilo = $conn->query("SELECT LAST_INSERT_ID()")->fetchColumn();
        if (! $idProfilo) {
            throw new Exception("Il profilo non è stato creato: controlla permessi o tipo progetto.");
        }

        // e) Inserisco la skill richiesta
        $stmtR = $conn->prepare("
            INSERT INTO RICHIEDE (Livello, Id_Profilo, Competenza_Skill)
            VALUES (:livello, :idProfilo, :competenza)
        ");
        $stmtR->execute([
            ':livello'    => $livello,
            ':idProfilo'  => $idProfilo,
            ':competenza' => $competenza
        ]);

        // f) Log su MongoDB
        writeLog(
            'Aggiunta profilo e skill richiesta',
            [
                'profilo'   => $nomeProfilo,
                'idProfilo' => $idProfilo,
                'progetto'  => $nomeProgetto,
                'skill'     => $competenza,
                'livello'   => $livello
            ]
        );

        // g) Redirect per evitare doppio submit
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;

    } catch (PDOException $e) {
        die("Errore PDO nell'inserimento del profilo/skill: " . $e->getMessage());
    } catch (Exception $e) {
        die("Errore: " . $e->getMessage());
    }
}


// 5) Include la navbar solo dopo gestione POST
include_once 'navbar.php';


// 6) Recupera le reward
try {
    $stmtR = $conn->prepare(
        "SELECT Codice, Descrizione, Foto
         FROM REWARD
         WHERE Nome_Progetto = :nome"
    );
    $stmtR->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtR->execute();
    $rewards = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    $stmtR->closeCursor();
} catch (PDOException $e) {
    die("Errore nella query delle reward: " . $e->getMessage());
}

// 7) Recupera i commenti
try {
    $stmtC = $conn->prepare(
        "SELECT Data, Testo, Email_Utente
         FROM COMMENTO
         WHERE Nome_Progetto = :nome
         ORDER BY Data DESC"
    );
    $stmtC->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtC->execute();
    $comments = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    $stmtC->closeCursor();
} catch (PDOException $e) {
    die("Errore nel recupero dei commenti: " . $e->getMessage());
}


// 8) Recupera le skill presenti nel db
// Recupera tutte le skill disponibili escluse quelle già associate all'utente
$availableSkillsQuery = "
    SELECT Competenza 
    FROM SKILL 
    ORDER BY Competenza
";
$stmtAvailableSkills = $conn->prepare($availableSkillsQuery);
$stmtAvailableSkills->execute();
$availableSkills = $stmtAvailableSkills->fetchAll(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($progetto["Nome"]) ?> | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php /* navbar già inclusa */ ?>

  <main class="container mt-5">
    <!-- Informazioni progetto -->
    <section class="mb-5">
      <h2><?= htmlspecialchars($progetto["Nome"]) ?></h2>
      <p><?= nl2br(htmlspecialchars($progetto["Descrizione"])) ?></p>
    </section>

        <!-- Finanziamento-->
    <!-- Bottone che manda via URL il nome del progetto -->
    <button
      onclick="window.location.href='finanziamento.php?nome=<?= urlencode($progetto['Nome']) ?>';"
      class="btn btn-success"
    >
     Finanzia questo progetto!
    </button>

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

  <!-- Controllo per vedere tipo del progetto --> 
  <!-- Controllo per vedere se creatore o meno --> 

   

  <!-- Inserire un nuovo profilo -->
  <h4>Aggiungi un nuovo profilo:</h4>
  <form method="POST" action="" class="mb-4">
    <div class="mb-3">
      <label for="profilo" class="form-label">Nome del profilo:</label>
      <input
        type="text"
        id="profilo"
        name="profilo"
        class="form-control"
        required
        maxlength="1000"
      >
    </div>
    <div class="mb-3">
      <label for="skills" class="form-label">Seleziona una skill:</label>
      <select name="skill" id="skills" class="form-select" required>
        <?php foreach ($availableSkills as $skill): ?>
          <option value="<?= htmlspecialchars($skill['Competenza']) ?>">
            <?= htmlspecialchars($skill['Competenza']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="level" class="form-label">Seleziona il livello:</label>
      <select name="level" id="level" class="form-select" required>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">
      Aggiungi profilo
    </button>
  </form>






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
