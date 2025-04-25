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
  <link rel="stylesheet" href="style.css" />
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
                echo '      <h5 class="card-title"><a href="progetto.php?nome=' . $nomeProgettoUrl . '">' . htmlspecialchars($row["Nome"]) . '</a></h5>';
                echo '      <p class="card-text">' . htmlspecialchars($row["Descrizione"]) . '</p>';
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
  </main>

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
