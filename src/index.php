<?php
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

// Scrivi un log per la visita alla homepage
writeLog('Visita homepage', 'Accesso alla homepage da parte di un utente');

// Esegui la stored procedure per ottenere i progetti con Stato = 'Aperto'
try {
    $stmt = $mysqlConn->query("CALL GetAvailableProjects()");
    $progetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore durante l'esecuzione della stored procedure: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Homepage | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

  <main>
    <div class="container">

      <!-- Sezione Progetti -->
      <section class="mt-5">
        <h2 class="text-green">Progetti Attivi</h2>
        
        <?php
        if (count($progetti) > 0) {
            echo '<div class="row">';
            foreach ($progetti as $row) {
                // Utilizziamo urlencode() per passare il nome del progetto via URL in sicurezza
                $nomeProgettoUrl = urlencode($row["Nome"]);
                echo '<div class="col-md-4 mb-3">';
                echo '  <div class="card h-100 shadow-sm">';
                echo '    <div class="card-body">';
                echo '      <h5 class="card-title">' . htmlspecialchars($row["Nome"]) . '</h5>';
                echo '      <p class="card-text">' . htmlspecialchars($row["Descrizione"]) . '</p>';
                echo '    </div>';
                echo '    <ul class="list-group list-group-flush">';
                echo '      <li class="list-group-item"><strong>Inizio:</strong> ' . htmlspecialchars($row["Data_Inserimento"]) . '</li>';
                echo '      <li class="list-group-item"><strong>Scadenza:</strong> ' . htmlspecialchars($row["Data_Limite"]) . '</li>';
                echo '      <li class="list-group-item"><strong>Budget:</strong> €' . htmlspecialchars($row["Budget"]) . '</li>';
                echo '      <li class="list-group-item"><strong>Stato:</strong> ' . htmlspecialchars($row["Stato"]) . '</li>';
                echo '      <li class="list-group-item"><strong>Tipo:</strong> ' . htmlspecialchars($row["Tipo"]) . '</li>';
                echo '    </ul>';
                // Bottone per finanziare il progetto, punta a finanziamento.php
                echo '    <div class="card-body text-center">';
                echo '      <a href="finanziamento.php?nome=' . $nomeProgettoUrl . '" class="btn btn-primary">Finanzia Progetto</a>';
                echo '    </div>';
                echo '  </div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">Non sono presenti progetti attivi al momento.</div>';
        }
        ?>
      </section>

      <!-- Altre sezioni della homepage -->
      <section class="mt-5">
        <h3>Classifica Creatori</h3>
        <ol class="list-group list-group-numbered shadow-sm rounded-lg">
          <li class="list-group-item">Noe</li>
          <li class="list-group-item">Ale</li>
        </ol>
      </section>

      <section class="mt-5">
        <h3>Progetti Quasi Finiti</h3>
        <ol class="list-group list-group-numbered shadow-sm rounded-lg">
          <li class="list-group-item">Basi di dati</li>
          <li class="list-group-item">Ingegneria</li>
        </ol>
      </section>

      <section class="mt-5">
        <h3>Classifica Utenti</h3>
        <ol class="list-group list-group-numbered shadow-sm rounded-lg">
          <li class="list-group-item">Leo</li>
          <li class="list-group-item">Marco</li>
        </ol>
      </section>

    </div>
  </main>

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
