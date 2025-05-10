<?php
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'auth.php';
include_once 'mysql.php';

$conn = getMySQLConnection();
$logCollection = getMongoDBConnection();

$nomeProgetto = isset($_GET['nome']) ? trim($_GET['nome']) : '';
if ($nomeProgetto === '') {
    die("Nome progetto non specificato.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commento'])) {
    requireLogin();
    $testoCommento = trim($_POST['commento']);
    $emailUtente = $_SESSION['id'];

    $stmtP = $conn->prepare(
        "SELECT Email_Creatore FROM PROGETTO WHERE Nome = :nome"
    );
    $stmtP->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtP->execute();
    $rowP = $stmtP->fetch(PDO::FETCH_ASSOC);
    $stmtP->closeCursor();

    if (!$rowP) {
        die("Progetto non trovato.");
    }
    $emailCreatore = $rowP['Email_Creatore'];

    try {
        $stmtI = $conn->prepare(
            "CALL InsertComment(:p_Testo, :p_Email_Utente, :p_Email_Creatore, :p_Nome_Progetto)"
        );
        $stmtI->bindParam(':p_Testo', $testoCommento, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Utente', $emailUtente, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Email_Creatore', $emailCreatore, PDO::PARAM_STR);
        $stmtI->bindParam(':p_Nome_Progetto', $nomeProgetto, PDO::PARAM_STR);
        $stmtI->execute();
        $stmtI->closeCursor();
        writeLog('Inserimento commento', "Utente $emailUtente ha commentato progetto $nomeProgetto");
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
    } catch (PDOException $e) {
        die("Errore nell'inserimento del commento: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profilo'])) {
    requireLogin();
    if (!isCreator()) {
        die("Non sei autorizzato a inserire profili in questo progetto.");
    }

    $nomeProfilo = trim($_POST['profilo']);
    $skill = trim($_POST['skill']);
    $livello = intval($_POST['level']);
    $emailCreatore = $_SESSION['id'];
    $nomeProgetto = $nomeProgetto;

    try {
        $stmtP = $conn->prepare("CALL InserisciProfilo(:p_Nome, :p_Progetto, :p_EmailCreatore)");
        $stmtP->bindParam(':p_Nome', $nomeProfilo, PDO::PARAM_STR);
        $stmtP->bindParam(':p_Progetto', $nomeProgetto, PDO::PARAM_STR);
        $stmtP->bindParam(':p_EmailCreatore', $emailCreatore, PDO::PARAM_STR);
        $stmtP->execute();
        $stmtP->closeCursor();

        $row = $stmtP->fetch(PDO::FETCH_ASSOC);
        $idProfilo = $row['NewId'] ?? null;
        $stmtP->closeCursor();

        if (!$idProfilo) {
            throw new Exception("Impossibile recuperare l'ID del profilo inserito.");
        }

        $stmtR = $conn->prepare(
            "INSERT INTO RICHIEDE (Id_Profilo, Competenza_Skill, Livello) VALUES (:p_IdProfilo, :p_Skill, :p_Livello)"
        );
        $stmtR->bindParam(':p_IdProfilo', $idProfilo, PDO::PARAM_INT);
        $stmtR->bindParam(':p_Skill', $skill, PDO::PARAM_STR);
        $stmtR->bindParam(':p_Livello', $livello, PDO::PARAM_INT);
        $stmtR->execute();
        $stmtR->closeCursor();
        writeLog('Aggiunta profilo', "Creatore $emailCreatore ha aggiunto il profilo '$nomeProfilo' (ID $idProfilo) al progetto $nomeProgetto con skill $skill livello $livello");
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
    } catch (PDOException $e) {
        die("Errore durante l'inserimento del profilo: " . $e->getMessage());
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nomeComponente'])) {
  requireLogin();
  if (!isCreator()) {
      die("Non sei autorizzato a inserire componenti in questo progetto.");
  }

  $nomeComponente = trim($_POST['nomeComponente']);
  $descrizioneComponente = trim($_POST['descrizioneComponente']);
  $prezzoComponente = floatval($_POST['prezzoComponente']);
  $quantitaComponente = intval($_POST['quantitaComponente']);
  $nomeProgettoHardware = $nomeProgetto; // stesso nome del progetto

  // Chiama la funzione per inserire il componente
  if (inserisciComponente($nomeComponente, $nomeProgettoHardware, $descrizioneComponente, $prezzoComponente, $quantitaComponente)) {
      writeLog('Aggiunta componente', "Creatore ha aggiunto il componente '$nomeComponente' al progetto $nomeProgetto");
      header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
      exit;
  } else {
      die("Errore nell'inserimento del componente.");
  }
}

// Elimina componente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminaComponente'])) {
  requireLogin();
  if (!isCreator()) {
      die("Non sei autorizzato a eliminare componenti in questo progetto.");
  }

  $nomeComponenteDaEliminare = trim($_POST['eliminaComponente']);
  if (eliminaComponente($nomeComponenteDaEliminare, $nomeProgetto)) {
      writeLog('Eliminazione componente', "Creatore ha eliminato il componente '$nomeComponenteDaEliminare' dal progetto $nomeProgetto");
      header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
      exit;
  } else {
      die("Errore nell'eliminazione del componente.");
  }
}


try {
    $stmt = $conn->prepare(
        "SELECT Nome, Descrizione, Email_Creatore, Tipo, Stato FROM PROGETTO WHERE Nome = :nome"
    );
    $stmt->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmt->execute();
    $progetto = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    die("Errore nel recupero del progetto: " . $e->getMessage());
}
if (!$progetto) {
    die("Progetto non trovato o non più aperto.");
}

try {
    $stmtF = $conn->prepare("SELECT Valore FROM FOTO WHERE Nome_Progetto = :nome");
    $stmtF->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtF->execute();
    $fotoProgetto = $stmtF->fetchAll(PDO::FETCH_ASSOC);
    $stmtF->closeCursor();
} catch (PDOException $e) {
    die("Errore nel recupero delle immagini del progetto: " . $e->getMessage());
}

include_once 'navbar.php';

try {
    $stmtR = $conn->prepare(
        "SELECT Codice, Descrizione, Foto FROM REWARD WHERE Nome_Progetto = :nome"
    );
    $stmtR->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtR->execute();
    $rewards = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    $stmtR->closeCursor();
} catch (PDOException $e) {
    die("Errore nella query delle reward: " . $e->getMessage());
}

try {
    $stmtC = $conn->prepare(
        "SELECT Data, Testo, Email_Utente FROM COMMENTO WHERE Nome_Progetto = :nome ORDER BY Data DESC"
    );
    $stmtC->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmtC->execute();
    $comments = $stmtC->fetchAll(PDO::FETCH_ASSOC);
    $stmtC->closeCursor();
} catch (PDOException $e) {
    die("Errore nel recupero dei commenti: " . $e->getMessage());
}

$availableSkillsQuery = "SELECT Competenza FROM SKILL ORDER BY Competenza";
$stmtAvailableSkills = $conn->prepare($availableSkillsQuery);
$stmtAvailableSkills->execute();
$availableSkills = $stmtAvailableSkills->fetchAll(PDO::FETCH_ASSOC);
$componenti = ottieniComponentiPerProgetto($nomeProgetto);
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($progetto["Nome"]) ?> | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container mt-5">
    <div id="infoProgetto">
      <section class="mb-5">
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

    <div id="finanziamento">
      <?php if ($progetto['Stato'] === 'Aperto'): ?>
        <button onclick="window.location.href='finanziamento.php?nome=<?= urlencode($progetto['Nome']) ?>';" class="btn btn-success my-4">Finanzia questo progetto!</button>
      <?php else: ?>
        <div class="alert alert-secondary mt-3">Il progetto è chiuso per i finanziamenti.</div>
      <?php endif; ?>
    </div>

    <div id="Reward">
      <div id="inserimentoReward">
        <?php if (isLoggedIn() && $_SESSION['id'] === $progetto['Email_Creatore']): ?>
          <a href="nuovaReward.php?nome=<?= urlencode($progetto['Nome']) ?>" class="btn btn-primary mb-3">Aggiungi nuova reward</a>
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

    <section class="mb-5">
      <h4>Commenti:</h4>
      <?php if (isLoggedIn()): ?>
        <form method="POST" class="mb-4">
          <div class="mb-3">
            <label for="commento" class="form-label">Inserisci commento:</label>
            <input type="text" id="commento" name="commento" class="form-control" required maxlength="1000">
          </div>
          <button type="submit" class="btn btn-secondary">Aggiungi</button>
        </form>
      <?php else: ?>
        <p><a href="login.php">Accedi</a> per inserire un commento.</p>
      <?php endif; ?>

      <?php if ($comments): ?>
        <ul class="list-group">
          <?php foreach ($comments as $c): ?>
            <li class="list-group-item">
              <small class="text-muted"><?= htmlspecialchars($c['Data']) ?> da <?= htmlspecialchars($c['Email_Utente']) ?></small>
              <p class="mb-0"><?= nl2br(htmlspecialchars($c['Testo'])) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Non ci sono commenti per questo progetto.</p>
      <?php endif; ?>
    </section>

    <?php if (isCreator()): ?>
      <?php if (($progetto['Tipo']) === 'Software'):?>
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
      <?php else: ?>
        <h4>Aggiungi un nuovo componente:</h4>
        <form method="POST" action="" class="mb-4">
            <div class="mb-3">
                <label for="nomeComponente" class="form-label">Nome del componente:</label>
                <input
                    type="text"
                    id="nomeComponente"
                    name="nomeComponente"
                    class="form-control"
                    required
                    maxlength="1000"
                >
            </div>
            <div class="mb-3">
                <label for="descrizioneComponente" class="form-label">Descrizione del componente:</label>
                <textarea
                  id="descrizioneComponente"
                  name="descrizioneComponente"
                  class="form-control"
                  required
                  maxlength="2000"
                ></textarea>
            </div>
            <div class="mb-3">
                <label for="prezzoComponente" class="form-label">Prezzo del componente:</label>
                <input
                  type="number"
                  id="prezzoComponente"
                  name="prezzoComponente"
                  class="form-control"
                  required
                  step="0.01"
                >
            </div>
            <div class="mb-3">
                <label for="quantitaComponente" class="form-label">Quantità del componente:</label>
                <input
                    type="number"
                    id="quantitaComponente"
                    name="quantitaComponente"
                    class="form-control"
                    required
                    min="1"
                >
            </div>
            <button type="submit" class="btn btn-primary">
                Aggiungi componente
            </button>
        </form>
        <section class="mb-5">
          <h4>Componenti del progetto:</h4>
          <?php if ($componenti): ?>
            <ul class="list-group">
              <?php foreach ($componenti as $componente): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?= htmlspecialchars($componente['Nome']) ?></strong>
                    <p><?= htmlspecialchars($componente['Descrizione']) ?></p>
                    <p>Prezzo: €<?= number_format($componente['Prezzo'], 2) ?> - Quantità: <?= number_format($componente['Quantità']) ?></p>
                  </div>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="eliminaComponente" value="<?= htmlspecialchars($componente['Nome']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>Nessun componente disponibile per questo progetto.</p>
          <?php endif; ?>
        </section>
    <?php endif; ?>
  <?php endif; ?>
</main>
<?php require_once 'footer.php'?>
</body>
</html>
