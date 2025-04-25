<?php
session_start();
include_once 'auth.php';
requireLogin();
requireCreator();
//require 'mongodb.php';
 
// Includi il file di connessione e navbar
include_once 'connection.php';
include_once 'navbar.php';

// Connessioni ai database
$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

// Scrivi un log per la visita alla homepage
//writeLog('Visita homepage', 'Accesso alla homepage da parte di un utente');

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

      <section>
        <button onclick="window.location.href='nuovoProgetto.php';"> Inserisci nuovo progetto </button>
        <h2> Progetti: </h2>
      </section>

      <!-- Sezione Progetti -->
      <section class="mt-5">
        <h2 class="text-green">Progetti Attivi</h2>
        
        <?php
          echo '<div class="row">';

          // Imposta il numero massimo di progetti da visualizzare
          $maxDisplay = 3;
          $totalProgetti = count($progetti);

          // Mostra solo i primi 3 progetti (o meno se totali < 3)
          for ($i = 0; $i < min($maxDisplay, $totalProgetti); $i++) {
              $row = $progetti[$i];
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

          // Se sono presenti più di 3 progetti, visualizza il bottone "Visualizza altri"
          if ($totalProgetti > 3) {
              echo '<div class="text-center mt-3">';
              echo '  <a href="visualizzaProgetti.php" class="btn btn-secondary">Visualizza altri</a>';
              echo '</div>';
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
