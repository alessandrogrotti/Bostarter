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

if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['profilo'], $_POST['skill'], $_POST['level'])
) {
  requireLogin();

  if (!isCreator()) {
      die("Solo il creatore del progetto può aggiungere profili.");
  }

  // Pulizia dati
  $nomeProfilo = trim($_POST['profilo']);
  $skills = $_POST['skill'];      // array di skill selezionate
  $levels = $_POST['level'];      // array dei livelli corrispondenti

  try {
      // 1. Inserisci il nuovo profilo nella tabella PROFILO
      $stmtProfilo = $conn->prepare("
          INSERT INTO PROFILO (Nome, Nome_ProgettoSoftware)
          VALUES (:nome, :nome_progetto)
      ");
      $stmtProfilo->bindParam(':nome', $nomeProfilo, PDO::PARAM_STR);
      $stmtProfilo->bindParam(':nome_progetto', $nomeProgetto, PDO::PARAM_STR);
      $stmtProfilo->execute();

      // 2. Recupera l'ID del profilo appena inserito
      $profiloId = $conn->lastInsertId();

      // 3. Prepariamo la query di inserimento competenze
      $stmtSkill = $conn->prepare("
          INSERT INTO RICHIEDE (Livello, Id_Profilo, Competenza_Skill)
          VALUES (:livello, :id_profilo, :skill)
      ");

      // 4. Inserisci solo le skill con livello valido (1-5)
      foreach ($skills as $index => $competenza) {
          if (
              !isset($levels[$index]) ||
              !is_numeric($levels[$index]) ||
              (int)$levels[$index] < 1 ||
              (int)$levels[$index] > 5
          ) {
              continue; // ignora skill senza livello valido
          }

          $livello = (int)$levels[$index];

          $stmtSkill->bindParam(':livello', $livello, PDO::PARAM_INT);
          $stmtSkill->bindParam(':id_profilo', $profiloId, PDO::PARAM_INT);
          $stmtSkill->bindParam(':skill', $competenza, PDO::PARAM_STR);
          $stmtSkill->execute();
      }

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
$profiliRaw = ottieniProfiliPerProgetto($nomeProgetto);
$profili = []; // profili raggruppati

foreach ($profiliRaw as $row) {
    $idProfilo = $row['Id'];
    if (!isset($profili[$idProfilo])) {
        $profili[$idProfilo] = [
            'Nome' => $row['Nome'],
            'Skills' => []
        ];
    }

    // Aggiungi la skill solo se esiste
    if (!empty($row['Competenza_Skill'])) {
        $profili[$idProfilo]['Skills'][] = [
            'Competenza_Skill' => $row['Competenza_Skill'],
            'Livello' => $row['Livello']
        ];
    }
}
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
        <form method="POST">
          <div class="mb-3">
            <label for="profilo" class="form-label">Nome del profilo:</label>
            <input type="text" id="profilo" name="profilo" class="form-control" required maxlength="1000">
          </div>

          <h5>Seleziona skill e livelli:</h5>
          <?php foreach ($availableSkills as $index => $skill): ?>
            <div class="mb-2 row align-items-center">
              <div class="col-sm-6">
                <label><?= htmlspecialchars($skill['Competenza']) ?></label>
                <input type="hidden" name="skill[]" value="<?= htmlspecialchars($skill['Competenza']) ?>">
              </div>
              <div class="col-sm-4">
                <select name="level[]" class="form-select">
                  <option value="">-- Nessun livello selezionato --</option>
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
          <?php endforeach; ?>
          <button type="submit" class="btn btn-primary mt-3">Crea profilo</button>
        </form>
        <section class="mb-5">
          <h4>Profili richiesti:</h4>
          <?php if ($profili): ?>
            <ul class="list-group">
              <?php foreach ($profili as $profilo): ?>
                <li class="list-group-item">
                <strong><?= htmlspecialchars($profilo['Nome']) ?></strong>
                <ul>
                  <?php foreach ($profilo['Skills'] as $skill): ?>
                    <li><?= htmlspecialchars($skill['Competenza_Skill']) ?> - Livello: <?= (int)$skill['Livello'] ?></li>
                  <?php endforeach; ?>
                </ul>
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
