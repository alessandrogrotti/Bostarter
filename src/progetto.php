<?php
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'auth.php';
include_once 'mysql.php';

$conn = getMySQLConnection();
$logCollection = getMongoDBConnection();

$nomeProgetto = isset($_GET['nome']) ? urldecode(trim($_GET['nome'])) : '';
if ($nomeProgetto === '') {
    die("Nome progetto non specificato.");
}

$dettagliProgetto = getDettagliProgetto($nomeProgetto);
if (!$dettagliProgetto) {
    die("Progetto non trovato.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['risposta']) && isset($_POST['id_commento'])) {
    requireLogin();

    $testoRisposta = trim($_POST['risposta']);
    $emailUtente = $_SESSION['id'];
    $idCommento = intval($_POST['id_commento']);

    $emailCreatore = getEmailCreatoreProgetto($nomeProgetto);
    if ($emailCreatore !== $emailUtente) {
        $_SESSION['error'] = "Solo il creatore del progetto può rispondere ai commenti.";
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
    }

    try {
        inviaRispostaCommento($testoRisposta, $emailUtente, $nomeProgetto, $idCommento);
        writeLog('Risposta commento', "Utente $emailUtente ha risposto al commento $idCommento nel progetto $nomeProgetto");
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
    } catch (Exception $e) {
        die("Errore nell'inserimento della risposta: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inviaCandidatura'])) {
  requireLogin();

  $emailUtente = $_SESSION['id'];
  $idProfilo = trim($_POST['inviaCandidatura']);

  try {
      inviaCandidatura($emailUtente, $idProfilo);
      $_SESSION['success'] = "Candidatura inviata con successo.";
      header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
      exit;
  } catch (Exception $e) {
      $_SESSION['error'] = $e->getMessage();
      header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
      exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commento'])) {
    requireLogin();
    $testoCommento = trim($_POST['commento']);
    $emailUtente = $_SESSION['id'];

    try {
        inviaCommento($testoCommento, $emailUtente, $nomeProgetto);
        writeLog('Inserimento commento', "Utente $emailUtente ha commentato progetto $nomeProgetto");
        header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
        exit;
    } catch (Exception $e) {
        die("Errore nell'inserimento del commento: " . $e->getMessage());
    }
}

$progetto = getDettagliProgetto($nomeProgetto);
if (!$progetto) {
    die("Progetto non trovato o non più aperto.");
}

$fotoProgetto = getFotoProgetto($nomeProgetto);
$rewards = getRewardsProgetto($nomeProgetto);
$comments = getCommentiProgetto($nomeProgetto);
$profili = ottieniProfiliPerProgetto($nomeProgetto);
$componenti = ottieniComponentiPerProgetto($nomeProgetto);

include_once 'navbar.php';
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
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-danger mt-4">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success mt-4">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

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
        <ul class="list-group mt-5 pt-2">
          <li class="list-group-item"><strong>Budget:</strong> <?= htmlspecialchars($dettagliProgetto['Budget']) ?> €</li>
          <li class="list-group-item"><strong>Data di chiusura:</strong> <?= htmlspecialchars($dettagliProgetto['Data_Limite']) ?></li>
          <li class="list-group-item"><strong>Stato:</strong> <?= htmlspecialchars($dettagliProgetto['Stato']) ?></li>
        </ul>
      </section>
    </div>

    <div id="finanziamento" class="mb-5">
      <?php if ($progetto['Stato'] === 'Aperto'): ?>
        <button onclick="window.location.href='finanziamento.php?nome=<?= urlencode($progetto['Nome']) ?>';" class="btn btn-success my-4">Finanzia questo progetto!</button>
      <?php else: ?>
        <div class="alert alert-secondary mt-3">Il progetto è chiuso per i finanziamenti.</div>
      <?php endif; ?>
    </div>

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

                <?php $response = getRispostaCommento($c['Id']); ?>

                <?php if ($response): ?>
                    <div class="mt-3">
                        <small class="text-muted"><?= htmlspecialchars($response['Data']) ?> da <?= htmlspecialchars($response['Email_Utente']) ?></small>
                        <p class="mb-1 fw-bold">Risposta:</p>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($response['Testo'])) ?></p>
                    </div>
                <?php else: ?>
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
        <p>Non ci sono commenti per questo progetto.</p>
      <?php endif; ?>
    </section>

    <?php if (($progetto['Tipo']) === 'Software'):?>
      <section class="mb-5">
        <h4>Profili richiesti:</h4>
        <?php if ($profili): ?>
            <?php
              $profiliAggregati = [];
              foreach ($profili as $row) {
                  $idProfilo = $row['Id'];
                  if (!isset($profiliAggregati[$idProfilo])) {
                      $profiliAggregati[$idProfilo] = [
                          'Id' => $idProfilo,
                          'Nome' => $row['Nome'],
                          'Skills' => []
                      ];
                  }
                  if (!empty($row['Competenza_Skill'])) {
                      $profiliAggregati[$idProfilo]['Skills'][] = [
                          'Competenza_Skill' => $row['Competenza_Skill'],
                          'Livello' => $row['Livello']
                      ];
                  }
              }
            ?>
            <ul class="list-group">
                <?php foreach ($profiliAggregati as $profilo): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($profilo['Nome']) ?></strong>
                                <ul class="mb-0">
                                    <?php foreach ($profilo['Skills'] as $skill): ?>
                                        <li><?= htmlspecialchars($skill['Competenza_Skill']) ?> - Livello: <?= (int)$skill['Livello'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <form method="POST" class="ms-3">
                                <input type="hidden" name="inviaCandidatura" value="<?= htmlspecialchars($profilo['Id']) ?>">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Invia candidatura
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nessun profilo disponibile per questo progetto.</p>
        <?php endif; ?>
      </section>
    <?php else: ?>
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
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Nessun componente disponibile per questo progetto.</p>
        <?php endif; ?>
      </section>
  <?php endif; ?>
</main>
<?php require_once 'footer.php'?>
</body>
</html>