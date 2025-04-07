<?php
// Includi il file di connessione
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
        <div class="alert alert-info">A breve potrai visualizzare tutti i progetti attivi qui!</div>
      </section>

      <!-- Classifica Creatori -->
      <section class="mt-5">
        <h3>Classifica Creatori</h3>
        <ol class="list-group list-group-numbered shadow-sm rounded-lg">
          <li class="list-group-item">Noe</li>
          <li class="list-group-item">Ale</li>
        </ol>
      </section>

      <!-- Progetti Quasi Finiti -->
      <section class="mt-5">
        <h3>Progetti Quasi Finiti</h3>
        <ol class="list-group list-group-numbered shadow-sm rounded-lg">
          <li class="list-group-item">Basi di dati</li>
          <li class="list-group-item">Ingegneria</li>
        </ol>
      </section>

      <!-- Classifica Utenti -->
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
