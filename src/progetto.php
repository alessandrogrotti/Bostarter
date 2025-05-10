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

// 2.b) Gestione invio risposta a un commento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['risposta']) && isset($_POST['id_commento'])) {
  requireLogin();

  $testoRisposta = trim($_POST['risposta']);
  $emailUtente   = $_SESSION['id'];
  $idCommento    = intval($_POST['id_commento']); // Recupera l'ID del commento

  try {
      $stmtR = $conn->prepare("CALL RispostaCommento(:p_Testo, :p_Email_Utente, :p_Nome_Progetto, :p_IdCommento)");
      $stmtR->bindParam(':p_Testo',         $testoRisposta, PDO::PARAM_STR);
      $stmtR->bindParam(':p_Email_Utente',  $emailUtente,   PDO::PARAM_STR);
      $stmtR->bindParam(':p_Nome_Progetto', $nomeProgetto,  PDO::PARAM_STR);
      $stmtR->bindParam(':p_IdCommento',    $idCommento,    PDO::PARAM_INT);
      $stmtR->execute();
      $stmtR->closeCursor();

      writeLog('Risposta commento', "Utente $emailUtente ha risposto al commento $idCommento nel progetto $nomeProgetto");

      header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
      exit;
  } catch (PDOException $e) {
      die("Errore nell'inserimento della risposta: " . $e->getMessage());
  }
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
                :p_Nome_Progetto
            )"
        );
        $stmtI->bindParam(':p_Testo',          $testoCommento, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Utente',   $emailUtente,   PDO::PARAM_STR);
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

// 4) Gestione inserimento profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profilo'])) {
  // a) Verifica autenticazione e che sia creatore
  requireLogin();
  if (!isCreator()) {
      die("Non sei autorizzato a inserire profili in questo progetto.");
  }

  // b) Pulisci i dati dal form
  $nomeProfilo      = trim($_POST['profilo']);
  $skill            = trim($_POST['skill']);
  $livello          = intval($_POST['level']);
  $emailCreatore    = $_SESSION['id'];  // presuppone che l'email del creatore sia in sessione
  $nomeProgetto     = $nomeProgetto;    // già definito più sopra da GET

  try {
      // c) Chiamata alla SP per inserire il profilo
      $stmtP = $conn->prepare("CALL InserisciProfilo(:p_Nome, :p_Progetto, :p_EmailCreatore)");
      $stmtP->bindParam(':p_Nome',            $nomeProfilo,   PDO::PARAM_STR);
      $stmtP->bindParam(':p_Progetto',        $nomeProgetto,  PDO::PARAM_STR);
      $stmtP->bindParam(':p_EmailCreatore',   $emailCreatore, PDO::PARAM_STR);
      $stmtP->execute();
      // dopo CALL, chiudi il cursor per poter fare altre query
      $stmtP->closeCursor();

      $row = $stmtP->fetch(PDO::FETCH_ASSOC);
      $idProfilo = $row['NewId'] ?? null;
      $stmtP->closeCursor();

      if (! $idProfilo) {
          throw new Exception("Impossibile recuperare l'ID del profilo inserito.");
      }

      // e) Inserimento nella tabella RICHIEDE
      $stmtR = $conn->prepare("
          INSERT INTO RICHIEDE (Id_Profilo, Competenza_Skill, Livello)
          VALUES (:p_IdProfilo, :p_Skill, :p_Livello)
      ");
      $stmtR->bindParam(':p_IdProfilo', $idProfilo, PDO::PARAM_INT);
      $stmtR->bindParam(':p_Skill',     $skill,     PDO::PARAM_STR);
      $stmtR->bindParam(':p_Livello',   $livello,   PDO::PARAM_INT);
      $stmtR->execute();
      $stmtR->closeCursor();

      // f) (Opzionale) Log su MongoDB
      writeLog(
          'Aggiunta profilo',
          "Creatore $emailCreatore ha aggiunto il profilo '$nomeProfilo' (ID $idProfilo) al progetto $nomeProgetto con skill $skill livello $livello"
      );

      // g) Redirect PRIMA di qualsiasi output
      header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
      exit;
  } catch (PDOException $e) {
      die("Errore durante l'inserimento del profilo: " . $e->getMessage());
  } catch (Exception $e) {
      die($e->getMessage());
  }
}


// 5) Recupera i dettagli del progetto
try {
  $stmt = $conn->prepare(
      "SELECT Nome, Descrizione, Email_Creatore, Tipo, Stato
       FROM PROGETTO
       WHERE Nome = :nome "
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

// Recupera immagini del progetto dalla tabella FOTO
try {
  $stmtF = $conn->prepare(
      "SELECT Valore FROM FOTO WHERE Nome_Progetto = :nome"
  );
  $stmtF->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
  $stmtF->execute();
  $fotoProgetto = $stmtF->fetchAll(PDO::FETCH_ASSOC);
  $stmtF->closeCursor();
} catch (PDOException $e) {
  die("Errore nel recupero delle immagini del progetto: " . $e->getMessage());
}

// 6) Include la navbar solo dopo gestione POST
include_once 'navbar.php';


// 7) Recupera le reward
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

// 8) Recupera i commenti
try {
  $stmtC = $conn->prepare(
    "SELECT c.Id, c.Data, c.Testo, c.Email_Utente
     FROM COMMENTO c
     WHERE c.Nome_Progetto = :nome
      AND NOT EXISTS (
         SELECT 1
         FROM RISPOSTA r
         WHERE Id_Risposta = c.Id
       )
     ORDER BY c.Data DESC"
  );

    $stmtC->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtC->execute();
    $comments = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    $stmtC->closeCursor();
} catch (PDOException $e) {
    die("Errore nel recupero dei commenti: " . $e->getMessage());
}


// 9) Recupera le skill presenti nel db
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

  <main class="container mt-5">
    <!-- Informazioni progetto -->
    <div id="infoProgetto" class="mb-5">
      <section>
        <h2><?= htmlspecialchars($progetto["Nome"]) ?></h2>
        <p><?= htmlspecialchars($progetto["Descrizione"]) ?></p>
        <?php if (!empty($fotoProgetto)): ?>
        <div class="row mt-4">
          <?php foreach ($fotoProgetto as $foto): ?>
            <div class="col-md-4 mb-3">
              <img src="<?= htmlspecialchars($foto['Valore']) ?>" alt="Foto progetto" class="img-fluid rounded shadow" style="width: 100%; max-width: 500px; height: auto;">
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </section>
    </div>

    <!-- Finanziamento -->
    <div id="finanziamento" class="mb-5">
      <?php if ($progetto['Stato'] === 'Aperto'): ?>
        <button
          onclick="window.location.href='finanziamento.php?nome=<?= urlencode($progetto['Nome']) ?>';"
          class="btn btn-success my-4"
        >
          Finanzia questo progetto!
        </button>
      <?php else: ?>
        <div class="alert alert-secondary mt-3">
          <i class="bi bi-lock-fill me-2"></i>
          Il progetto è chiuso per i finanziamenti.
        </div>
      <?php endif; ?>
    </div>

    <!-- Reward -->
    <div id="Reward" class="mb-5">
      <div id="inserimentoReward">
        <?php if (isLoggedIn() && $_SESSION['id'] === $progetto['Email_Creatore']): ?>
          <a
          href="nuovaReward.php?nome=<?= urlencode($progetto['Nome']) ?>"
          class="btn btn-primary mb-3"
          >
            Aggiungi nuova reward
          </a>
        <?php endif; ?>
      </div>

      <div id="listaReward">
        <section class="mb-5">
          <h4>Lista delle reward:</h4>
          <?php if ($rewards): ?>
            <ul class="list-group">
              <?php foreach ($rewards as $r): ?>
                <li class="list-group-item d-flex align-items-center">
                  <div class="me-3">
                    <?php if (!empty($r['Foto'])): ?>
                      <img src="<?= htmlspecialchars($r['Foto']) ?>" alt="Immagine reward" class="img-fluid" style="width: 50px; height: auto;">
                    <?php else: ?>
                      <span class="text-muted">Nessuna immagine</span>
                    <?php endif; ?>
                  </div>
                  <div>
                    <strong><?= htmlspecialchars($r['Codice']) ?></strong>: <?= htmlspecialchars($r['Descrizione']) ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>Nessuna reward disponibile.</p>
          <?php endif; ?>
        </section>
      </div>
    </div>

    <!-- Commento -->
    <section id="commento" class="mb-5">
      <?php if (isLoggedIn()): ?>
        <h4>Aggiungi un commento:</h4>
        <form method="POST" action="" class="mb-4">
          <div class="mb-3">
            <label for="commento" class="form-label">Il tuo commento:</label>
            <textarea
              id="commento"
              name="commento"
              class="form-control"
              required
              maxlength="1000"
              rows="3"
            ></textarea>
          </div>
          <button type="submit" class="btn btn-primary">
            Invia commento
          </button>
        </form>
      <?php else: ?>
        <p>
          <a href="login.php">Accedi</a> per lasciare un commento.
        </p>
      <?php endif; ?>
    </section>

    <!-- Commenti esistenti -->
    <section class="mb-5">
      <h4>Commenti:</h4>
      <?php if ($comments): ?>
        <ul class="list-group">
          <?php foreach ($comments as $c): ?>
            <li class="list-group-item">
                <small class="text-muted">
                    <?= htmlspecialchars($c['Data']) ?> da <?= htmlspecialchars($c['Email_Utente']) ?>
                </small>
                <p class="mb-1 fw-bold">Commento:</p>
                <p class="mb-0"><?= nl2br(htmlspecialchars($c['Testo'])) ?></p>

                <!-- Recupera le risposte per questo commento -->
                <?php
                    $stmtR = $conn->prepare(
                        "SELECT c.Testo, c.Data, c.Email_Utente
                        FROM COMMENTO c
                        INNER JOIN RISPOSTA r ON c.Id = r.Id_Risposta
                        WHERE r.Id_Commento = :id_commento"
                    );
                    $stmtR->bindParam(':id_commento', $c['Id'], PDO::PARAM_INT);
                    $stmtR->execute();
                    $response = $stmtR->fetch(PDO::FETCH_ASSOC);
                    $stmtR->closeCursor();
                ?>

                <!-- Se il commento ha una risposta, visualizzala -->
                <?php if ($response): ?>
                    <div class="mt-3">
                        <small class="text-muted"><?= htmlspecialchars($response['Data']) ?> da <?= htmlspecialchars($response['Email_Utente']) ?></small>
                        <p class="mb-1 fw-bold">Risposta:</p>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($response['Testo'])) ?></p>
                    </div>
                <?php else: ?>
                    <!-- Se non ci sono risposte, mostra il campo di testo per rispondere -->
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="" class="mt-3">
                            <div class="mb-3">
                                <label for="risposta_<?= $c['Id'] ?>" class="form-label">Rispondi a questo commento:</label>
                                <textarea
                                    id="risposta_<?= $c['Id'] ?>"
                                    name="risposta"
                                    class="form-control"
                                    required
                                    maxlength="1000"
                                    rows="3"
                                ></textarea>
                            </div>
                            <input type="hidden" name="id_commento" value="<?= $c['Id'] ?>">
                            <button type="submit" class="btn btn-primary">Rispondi</button>
                        </form>
                    <?php else: ?>
                        <p>
                            <a href="login.php">Accedi</a> per rispondere a questo commento.
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Ancora nessun commento.</p>
      <?php endif; ?>
    </section>

    <!-- Sezione per aggiungere un profilo se il progetto è di tipo Software -->
    <?php if (($progetto['Tipo']) === 'Software'): ?>
      <?php if (isCreator()): ?>
        <section class="mb-5">
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
                  <option value="<?= htmlspecialchars($skill['Competenza'], ENT_QUOTES) ?>">
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
        </section>
      <?php endif; ?>

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
    <?php endif; ?>

  </main>

  <footer class="text-center mt-5 py-3 bg-light">
    <p>Progetto Bostarter &copy; <?= date('Y') ?></p>
  </footer>

</body>
</html>
