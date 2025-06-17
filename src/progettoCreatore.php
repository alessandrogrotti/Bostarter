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

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['profilo'], $_POST['skill'], $_POST['level'])
) {
    requireLogin();
    if (! isCreator()) {
        die("Solo il creatore del progetto può aggiungere profili.");
    }

    // pulizia dati
    $nomeProfilo   = trim($_POST['profilo']);
    $competenza    = $_POST['skill'];
    $livello       = (int) $_POST['level'];

    // chiamo la stored procedure
    try {
        $stmt = $conn->prepare(
            "CALL InserisciProfiloRichiede(
                :p_Nome,
                :p_Nome_ProgettoSoftware,
                :p_Email_Creatore,
                :p_Livello,
                :p_Competenza_Skill
            )"
        );
        $stmt->bindParam(':p_Nome',                     $nomeProfilo,   PDO::PARAM_STR);
        $stmt->bindParam(':p_Nome_ProgettoSoftware',    $nomeProgetto,  PDO::PARAM_STR);
        $stmt->bindParam(':p_Email_Creatore',           $progetto['Email_Creatore'], PDO::PARAM_STR);
        $stmt->bindParam(':p_Livello',                  $livello,       PDO::PARAM_INT);
        $stmt->bindParam(':p_Competenza_Skill',         $competenza,    PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        writeLog(
            'Inserimento profilo',
            "Creatore {$_SESSION['id']} ha aggiunto profilo '$nomeProfilo' al progetto '$nomeProgetto'"
        );

        header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
        exit;
    } catch (PDOException $e) {
        die("Errore nell'inserimento del profilo: " . $e->getMessage());
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
      header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
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
      header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
      exit;
  } else {
      die("Errore nell'eliminazione del componente.");
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminaProfilo'])) {
  requireLogin();
  if (!isCreator()) {
      die("Non sei autorizzato a eliminare profilo in questo progetto.");
  }

  $IdProfiloDaEliminare = trim($_POST['eliminaProfilo']);
  if (eliminaProfilo($IdProfiloDaEliminare)) {
      writeLog('Eliminazione profilo', "Creatore ha eliminato il profilo con id '$IdProfiloDaEliminare'");
      header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
      exit;
  } else {
      die("Errore nell'eliminazione del profilo.");
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

$availableSkillsQuery = "SELECT Competenza FROM SKILL ORDER BY Competenza";
$stmtAvailableSkills = $conn->prepare($availableSkillsQuery);
$stmtAvailableSkills->execute();
$availableSkills = $stmtAvailableSkills->fetchAll(PDO::FETCH_ASSOC);
$profili = ottieniProfiliPerProgetto($nomeProgetto);
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
        <h1><?= htmlspecialchars($progetto["Nome"]) ?></h1>
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

    <div id="finanziamento" class="mb-5">
      <?php if ($progetto['Stato'] === 'Aperto'): ?>
        <div class="alert alert-secondary mt-3">Il progetto è aperto per i finanziamenti.</div>  
      <?php else: ?>
        <div class="alert alert-secondary mt-3">Il progetto è chiuso per i finanziamenti.</div>
      <?php endif; ?>
    </div>

    <div id="Reward" class="mb-5">
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
          <?php if ($profili): ?>
            <ul class="list-group">
              <?php foreach ($profili as $profilo): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?= htmlspecialchars($profilo['Nome']) ?></strong>
                    <p><?= htmlspecialchars($profilo['Competenza_Skill']) ?></p>
                    <p>Livello: <?= number_format($profilo['Livello'], 2) ?></p>
                  </div>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="eliminaProfilo" value="<?= htmlspecialchars($profilo['Id']) ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p>Nessun profilo disponibile per questo progetto.</p>
          <?php endif; ?>
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
