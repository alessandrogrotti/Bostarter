<?php
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'auth.php';
include_once 'mysql.php';


$nomeProgetto = isset($_GET['nome']) ? urldecode(trim($_GET['nome'])) : '';
if ($nomeProgetto === '') {
  die("Nome progetto non specificato.");
}

$errorMessage = '';

try {
    $progetto = ottieniDettagliProgetto($nomeProgetto);
    if (!$progetto) {
        throw new Exception("Progetto non trovato o non più aperto.");
    }

    $fotoProgetto = ottieniFotoProgetto($nomeProgetto);
    $rewards = ottieniRewardPerProgetto($nomeProgetto);
    $availableSkills = ottieniCompetenze();
    $profili = ottieniProfiliPerProgetto($nomeProgetto);
    foreach ($profili as $idProfilo => $profilo) {
        $profili[$idProfilo]['Candidature'] = ottieniCandidaturePerProfilo($idProfilo);
    }
    $componenti = ottieniComponentiPerProgetto($nomeProgetto);
} catch (Exception $e) {
    writeLog('Errore caricamento dati progetto', ['errore' => $e->getMessage()]);
    $errorMessage = $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['profilo'], $_POST['skill'], $_POST['level'])) {
            requireLogin();
            if (!isCreator()) {
                throw new Exception("Solo il creatore del progetto può aggiungere profili.");
            }

            $nomeProfilo = trim($_POST['profilo']);
            $skills = $_POST['skill'];
            $levels = $_POST['level'];

            if (!inserisciProfiloConCompetenze($nomeProfilo, $nomeProgetto, $skills, $levels)) {
                throw new Exception("Errore nell'inserimento del profilo.");
            }

            writeLog('Inserimento profilo', "Creatore {$_SESSION['id']} ha aggiunto profilo '$nomeProfilo' al progetto '$nomeProgetto'");
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        }

        if (isset($_POST['nomeComponente'])) {
            requireLogin();
            if (!isCreator()) {
                throw new Exception("Non sei autorizzato a inserire componenti in questo progetto.");
            }

            $nomeComponente = trim($_POST['nomeComponente']);
            $descrizioneComponente = trim($_POST['descrizioneComponente']);
            $prezzoComponente = floatval($_POST['prezzoComponente']);
            $quantitaComponente = intval($_POST['quantitaComponente']);

            if (!inserisciComponente($nomeComponente, $nomeProgetto, $descrizioneComponente, $prezzoComponente, $quantitaComponente)) {
                throw new Exception("Errore nell'inserimento del componente.");
            }

            writeLog('Aggiunta componente', "Creatore ha aggiunto il componente '$nomeComponente' al progetto $nomeProgetto");
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        }

        if (isset($_POST['eliminaComponente'])) {
            requireLogin();
            if (!isCreator()) {
                throw new Exception("Non sei autorizzato a eliminare componenti in questo progetto.");
            }

            $nomeComponenteDaEliminare = trim($_POST['eliminaComponente']);
            if (!eliminaComponente($nomeComponenteDaEliminare, $nomeProgetto)) {
                throw new Exception("Errore nell'eliminazione del componente.");
            }

            writeLog('Eliminazione componente', "Creatore ha eliminato il componente '$nomeComponenteDaEliminare' dal progetto $nomeProgetto");
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        }

        if (isset($_POST['eliminaProfilo'])) {
            requireLogin();
            if (!isCreator()) {
                throw new Exception("Non sei autorizzato a eliminare profilo in questo progetto.");
            }

            $IdProfiloDaEliminare = trim($_POST['eliminaProfilo']);
            if (!eliminaProfilo($IdProfiloDaEliminare)) {
                throw new Exception("Errore nell'eliminazione del profilo.");
            }

            writeLog('Eliminazione profilo', "Creatore ha eliminato il profilo con id '$IdProfiloDaEliminare'");
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        }

        if (isset($_POST['gestisciCandidatura'])) {
            $idCandidatura = intval($_POST['idCandidatura']);
            $stato = $_POST['stato'];

            if (!gestisciCandidatura($idCandidatura, $stato)) {
                throw new Exception("Errore nella gestione della candidatura.");
            }

            writeLog('Gestione candidatura', "Creatore ha aggiornato la candidatura con ID '$idCandidatura' a '$stato'");
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        }
    } catch (Exception $e) {
        writeLog('Errore gestione richiesta POST', ['errore' => $e->getMessage()]);
        $errorMessage = $e->getMessage();
    }
}

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
    <div id="infoProgetto">
      <section class="mb-5">
        <h1><?= htmlspecialchars($progetto["Nome"]) ?></h1>
        <p><?= htmlspecialchars($progetto["Descrizione"]) ?></p>
        <?php if (!empty($fotoProgetto)): ?>
        <div class="row mt-4">
          <?php foreach ($fotoProgetto as $foto => $fotoUrl): ?>
            <div class="col-md-4 mb-3">
              <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto progetto" class="img-fluid rounded" style="width: 100%; max-width: 500px; height: auto;">
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </section>
    </div>

    
    <div id="finanziamento" class="mb-5">
      <?php if ($progetto['Stato'] === 'Aperto'): ?>
        <div class="alert alert-primary mt-3">Il progetto è aperto per i finanziamenti.</div>  
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
            <?php
            $profiliAggregati = [];
            foreach ($profili as $row) {
                $idProfilo = $row['Id'];
                if (!isset($profiliAggregati[$idProfilo])) {
                    $profiliAggregati[$idProfilo] = [
                        'Id' => $idProfilo,
                        'Nome' => $row['Nome'],
                        'Skills' => [],
                        'Candidature' => ottieniCandidaturePerProfilo($idProfilo) // Ottieni candidature per il profilo
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
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Lista dei profili</h5>
                    <div class="row">
                        <?php foreach ($profiliAggregati as $profilo): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card profile-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title"><?= htmlspecialchars($profilo['Nome']) ?></h5>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($profilo['Skills'] as $skill): ?>
                                                        <li><?= htmlspecialchars($skill['Competenza_Skill']) ?> - Livello: <?= (int)$skill['Livello'] ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            <form method="POST" class="ms-3">
                                                <input type="hidden" name="eliminaProfilo" value="<?= htmlspecialchars($profilo['Id']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Rimuovi</button>
                                            </form>
                                        </div>
                                        <?php if (!empty($profilo['Candidature'])): ?>
                                            <table class="table mt-3">
                                                <thead>
                                                    <tr>
                                                        <th>Utente</th>
                                                        <th>Stato</th>
                                                        <th>Azioni</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($profilo['Candidature'] as $candidatura): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($candidatura['Nickname']) ?></td>
                                                            <td><?= htmlspecialchars($candidatura['Stato']) ?></td>
                                                            <td>
                                                                <?php if ($candidatura['Stato'] === 'In attesa'): ?>
                                                                    <form method="POST" class="d-inline">
                                                                        <input type="hidden" name="gestisciCandidatura" value="1">
                                                                        <input type="hidden" name="idCandidatura" value="<?= htmlspecialchars($candidatura['Id']) ?>">
                                                                        <button type="submit" name="stato" value="Accettata" class="btn btn-success btn-sm">Accetta</button>
                                                                        <button type="submit" name="stato" value="Rifiutata" class="btn btn-danger btn-sm">Rifiuta</button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <p class="mt-3">Nessuna candidatura per questo profilo.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
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

  <?php if ($errorMessage): ?>
    <div class="alert alert-danger mb-4">
      <?= htmlspecialchars($errorMessage) ?>
    </div>
  <?php endif; ?>
</main>
<?php require_once 'footer.php'?>
</body>
</html>

