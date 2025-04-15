<?php
// Recupera il nome del progetto passato via GET e lo sanitizza per la sicurezza.
$nomeProgetto = isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : 'Progetto Sconosciuto';

// Includi il file di connessione e navbar
include 'connection.php';
include 'navbar.php';

// Connessioni ai database
$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

// Funzione per scrivere un log su MongoDB
function writeLog($action, $details) {
    global $logCollection;
    
    $logEntry = [
        'action' => $action,
        'details' => $details,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
    ];

    $bulkWrite = new MongoDB\Driver\BulkWrite;
    $bulkWrite->insert($logEntry);

    try {
        $logCollection->executeBulkWrite('Bostarter.logs', $bulkWrite);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Errore durante la scrittura del log: " . $e->getMessage());
    }
}

// Scrivi un log per la visita alla pagina di finanziamento
writeLog('Visita finanziamento', "Accesso alla pagina di finanziamento per il progetto \"$nomeProgetto\"");

// Esegui una query per ottenere le reward del progetto corrente
try {
    $stmt = $mysqlConn->prepare("SELECT Codice, Descrizione FROM REWARD WHERE Nome_Progetto = :nomeProgetto");
    $stmt->bindParam(':nomeProgetto', $nomeProgetto);
    $stmt->execute();
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore durante l'esecuzione della query: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Finanziamento | Bostarter</title>
  <!-- Inclusione Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <main class="container mt-5">
    <div class="card">
      <div class="card-header">
        <h3>Finanzia il progetto "<?php echo $nomeProgetto; ?>"</h3>
      </div>
      <div class="card-body">
        <form action="process_finanziamento.php" method="POST">
          <!-- Campo importo -->
          <div class="mb-3">
            <label for="importo" class="form-label">Importo:</label>
            <input type="text" class="form-control" id="importo" name="importo" required>
          </div>
          <!-- Selezione reward -->
          <div class="mb-3">
            <label class="form-label">Scegli una reward:</label>
            <ul class="list-group">
              <?php
              if(count($rewards) > 0) {
                  foreach($rewards as $reward) {
                      // Uso del codice della reward come valore per il radio button.
                      $codiceReward = htmlspecialchars($reward['Codice']);
                      $descrReward = htmlspecialchars($reward['Descrizione']);
                      echo '<li class="list-group-item">';
                      echo '  <div class="form-check">';
                      echo '    <input class="form-check-input" type="radio" name="reward" id="reward_' . $codiceReward . '" value="' . $codiceReward . '" required>';
                      echo '    <label class="form-check-label" for="reward_' . $codiceReward . '">';
                      echo '      ' . $descrReward;
                      echo '    </label>';
                      echo '  </div>';
                      echo '</li>';
                      
                  }
              } else {
                  echo '<li class="list-group-item">Non sono presenti reward per questo progetto.</li>';
              }
              ?>
            </ul>
          </div>
          <!-- Campo hidden per mantenere il nome del progetto -->
          <input type="hidden" name="nome_progetto" value="<?php echo $nomeProgetto; ?>">
          <button type="submit" class="btn btn-primary">Invia</button>
        </form>
      </div>
    </div>
  </main>

  <footer class="text-center mt-5 py-3 bg-light">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>
</body>
</html>
