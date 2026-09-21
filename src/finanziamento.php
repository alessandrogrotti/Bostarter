<?php
include_once 'auth.php';
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'mysql.php';

requireLogin();

$nome_progetto = isset($_GET['nome']) ? urldecode(trim($_GET['nome'])) : '';
if ($nome_progetto === '') {
    $_SESSION['error'] = "Nome progetto non specificato. - progetto.php";
    header("Location: index.php");
    exit;
}

$rewards = [];
$finanziamentoEffettuato = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $rewards = ottieniRewardPerProgetto($nome_progetto);

        $email_utente = $_SESSION['id'];
        $finanziamentoEffettuato = verificaFinanziamentoOggi($email_utente, $nome_progetto);

        if (empty($rewards)) {
            $_SESSION['error'] = "Questo progetto non ha reward disponibili. Non è possibile effettuare finanziamenti.";
        } elseif ($finanziamentoEffettuato) {
            $_SESSION['error'] = "Hai già registrato un finanziamento per “{$nome_progetto}” oggi. Torna domani!";
        }
    } catch (Exception $e) {
        writeLog('Errore query reward', ['progetto' => $nome_progetto, 'errore' => $e->getMessage()]);
        $_SESSION['error'] = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $importo = isset($_POST['importo']) ? floatval($_POST['importo']) : 0;
    $codice_reward = isset($_POST['reward']) ? htmlspecialchars(trim($_POST['reward']), ENT_QUOTES) : null;
    $email_utente = $_SESSION['id'];

    writeLog('Tentativo finanziamento', [
        'utente' => $email_utente,
        'progetto' => $nome_progetto,
        'reward' => $codice_reward,
        'importo' => $importo
    ]);

    try {
        eseguiFinanziamento($email_utente, $importo, $nome_progetto, $codice_reward);
        $_SESSION['success'] = "Finanziamento di €" . number_format($importo, 2) . " per “{$nome_progetto}” con reward “{$codice_reward}” registrato con successo.";
        header("Location: progetto.php?nome=" . urlencode($nome_progetto));
        exit;
    } catch (Exception $e) {
        writeLog('Errore finanziamento', [
            'utente' => $email_utente,
            'progetto' => $nome_progetto,
            'reward' => $codice_reward,
            'importo' => $importo,
            'errore' => $e->getMessage()
        ]);
        $_SESSION['error'] = $e->getMessage();
        header("Location: progetto.php?nome=" . urlencode($nome_progetto));
        exit;
    }
}

try {
    $progetto = getDettagliProgetto($nome_progetto);
    if (!$progetto) {
        throw new Exception("Progetto non trovato o non più aperto.");
    }
} catch (Exception $e) {
    writeLog('Errore caricamento progetto', ['progetto' => $nome_progetto, 'errore' => $e->getMessage()]);
    $_SESSION['error'] = $e->getMessage();
}
?>
<?php
include 'navbar.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Finanziamento | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container mt-5">
  <?php if ($_SESSION['error']): ?>
      <div class="alert alert-danger mt-4">
        <?= htmlspecialchars($_SESSION['error']) ?>
      </div>
      <?php $_SESSION['error'] = ''; ?>
    <?php else: ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success mt-4">
        <?= htmlspecialchars($_SESSION['success']) ?>
      </div>
      <?php $_SESSION['success'] = ''; ?>
    <?php endif; ?>

    <h2 class="mb-4">Finanzia il progetto "<strong><?= htmlspecialchars($nome_progetto) ?></strong>"</h2>

    <?php if (!empty($rewards) && !$finanziamentoEffettuato): ?>
      <form action="finanziamento.php?nome=<?= urlencode($nome_progetto) ?>" method="POST">
        <div class="mb-4">
          <label for="importo" class="form-label">Importo (€):</label>
          <input
            type="number"
            step="0.01"
            min="0.01"
            class="form-control"
            id="importo"
            name="importo"
            required
          >
        </div>
        <div class="mb-4">
          <label class="form-label">Scegli una reward:</label>
          <ul class="list-group">
            <?php foreach ($rewards as $r): 
              $cod = htmlspecialchars($r['Codice'], ENT_QUOTES);
              $desc= htmlspecialchars($r['Descrizione'], ENT_QUOTES);
            ?>
              <li class="list-group-item">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="reward"
                    id="reward_<?= $cod ?>"
                    value="<?= $cod ?>"
                    required
                  >
                  <label class="form-check-label" for="reward_<?= $cod ?>">
                    <?= $desc ?>
                  </label>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <button type="submit" class="btn btn-primary">Conferma finanziamento</button>
      </form>
    <?php else: ?>
      <p class="text-danger">Non è possibile finanziare questo progetto.</p>
    <?php endif; ?>
    <?php endif; ?>
  </main>
  <footer class="text-center mt-5 py-3 bg-light">
    <p>Bostarter &copy; <?= date('Y') ?></p>
  </footer>
</body>
</html>
