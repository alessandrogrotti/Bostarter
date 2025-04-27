<?php
session_start();
include_once 'auth.php';         // requireLogin(), requireCreator()
include_once 'connection.php';   // getMySQLConnection()
include_once 'mongodb.php';      // writeLog()
requireLogin();

$mysqlConn     = getMySQLConnection();
$logCollection= getMongoDBConnection();

// Recupera e pulisce il nome progetto da GET o POST (in POST il redirect avrà già settato)
// usiamo trim + htmlspecialchars
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_progetto = isset($_POST['nome_progetto']) 
        ? htmlspecialchars(trim($_POST['nome_progetto']), ENT_QUOTES) 
        : null;
} else {
    $nome_progetto = isset($_GET['nome']) 
        ? htmlspecialchars(trim($_GET['nome']), ENT_QUOTES) 
        : 'Progetto Sconosciuto';
}

// Se GET: carica le reward
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $mysqlConn->prepare(
            "SELECT Codice, Descrizione 
             FROM REWARD 
             WHERE Nome_Progetto = :nome"
        );
        $stmt->execute([':nome' => $nome_progetto]);
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Errore query reward: " . $e->getMessage());
    }
}

// Se POST: gestisci il finanziamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $importo      = isset($_POST['importo']) ? floatval($_POST['importo']) : 0;
    $codice_reward= isset($_POST['reward'])   ? htmlspecialchars(trim($_POST['reward']), ENT_QUOTES) : null;
    $email_utente = $_SESSION['id'];

    // Basic validation
    if ($importo <= 0 || !$nome_progetto || !$codice_reward) {
        $_SESSION['fin_err'] = "Dati mancanti o non validi.";
        header("Location: finanziamento.php?nome=" . urlencode($nome_progetto));
        exit;
    }

    // Log su MongoDB
    writeLog(
        'Finanziamento',
        [
          'utente'   => $email_utente,
          'progetto' => $nome_progetto,
          'reward'   => $codice_reward,
          'importo'  => $importo
        ]
    );

    // Chiamata alla SP
    try {
        $sql = "CALL FinanceProject(:p_Email_Utente, :p_Importo, :p_Nome_Progetto, :p_Codice_Reward)";
        $stmt = $mysqlConn->prepare($sql);
        $stmt->bindParam(':p_Email_Utente', $email_utente);
        $stmt->bindParam(':p_Importo',      $importo);
        $stmt->bindParam(':p_Nome_Progetto',$nome_progetto);
        $stmt->bindParam(':p_Codice_Reward',$codice_reward);
        $stmt->execute();

        $_SESSION['fin_ok'] = "Grazie! Il tuo finanziamento da €{$importo} è andato a buon fine.";
    } catch (PDOException $e) {
      // Se è un errore di chiave duplicata (1062), mostriamo un messaggio dedicato
      if ($e->errorInfo[1] === 1062) {
          $_SESSION['fin_err'] = 
              "Hai già registrato un finanziamento per “{$nome_progetto}” oggi. Torna domani!";
      } else {
          $_SESSION['fin_err'] = "Errore durante il finanziamento: " . $e->getMessage();
      }
  }

    // Redirect per Post/Redirect/Get
    header("Location: finanziamento.php?nome=" . urlencode($nome_progetto));
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Finanziamento | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php include 'navbar.php'; ?>
  <main class="container mt-5">
    <?php if (!empty($_SESSION['fin_ok'])): ?>
      <div class="alert alert-success">
        <?= $_SESSION['fin_ok']; unset($_SESSION['fin_ok']); ?>
      </div>
    <?php elseif (!empty($_SESSION['fin_err'])): ?>
      <div class="alert alert-danger">
        <?= $_SESSION['fin_err']; unset($_SESSION['fin_err']); ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3>Finanzia il progetto “<?= $nome_progetto ?>”</h3>
      </div>
      <div class="card-body">
        <form action="finanziamento.php" method="POST">
          <div class="mb-3">
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
          <div class="mb-3">
            <label class="form-label">Scegli una reward:</label>
            <ul class="list-group">
              <?php if (count($rewards)): ?>
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
              <?php else: ?>
                <li class="list-group-item text-muted">
                  Nessuna reward disponibile per questo progetto.
                </li>
              <?php endif; ?>
            </ul>
          </div>
          <input
            type="hidden"
            name="nome_progetto"
            value="<?= $nome_progetto ?>"
          >
          <button type="submit" class="btn btn-primary">Conferma finanziamento</button>
        </form>
      </div>
    </div>
  </main>
  <footer class="text-center mt-5 py-3 bg-light">
    <p>Bostarter &copy; <?= date('Y') ?></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
