<?php
include_once 'auth.php';

session_start();
requireAdmin();
?>

<?php
include_once 'navbar.php';
include_once 'mongodb.php';
include_once 'mysql.php';


writeLog('Visita pagina competenza', 'Accesso alla pagina per aggiunta competenza da parte dell’amministratore');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['competenza'])) {
    $nomeCompetenza = $_POST['competenza'];
    if (inserisciCompetenza($nomeCompetenza)) {
        writeLog('Inserimento competenza', "Competenza '$nomeCompetenza' aggiunta al database.");
    } else {
        writeLog('Errore inserimento competenza', "Errore nell'inserimento della competenza '$nomeCompetenza'.");
    }
}

if (isset($_GET['removeCompetenza'])) {
    $competenzaToRemove = $_GET['removeCompetenza'];
    if (eliminaCompetenza($competenzaToRemove)) {
        writeLog('Eliminazione competenza', "Competenza '$competenzaToRemove' eliminata dal database.");
    } else {
        writeLog('Errore eliminazione competenza', "Errore nell'eliminazione della competenza '$competenzaToRemove'.");
    }
}

$competenze = ottieniCompetenze();
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/style.css" />
  <title>Amministratore - Aggiunta Competenza</title>
</head>
<body>

  <main class="container mt-5">
    <h2>Aggiungi una competenza</h2>
    <form method="POST" action="amministratore.php">
      <div class="mb-3">
        <label for="competenza" class="form-label">Inserisci competenza:</label>
        <input type="text" id="competenza" name="competenza" class="form-control" required />
      </div>
      <button type="submit" class="btn btn-success">Inserisci</button>
    </form>

    <h3 class="mt-4">Lista delle competenze:</h3>
    <ul class="list-group mt-3">
      <?php foreach ($competenze as $competenza): ?>
        <li class="list-group-item">
          <?php echo htmlspecialchars($competenza['Competenza']); ?>
          <a href="?removeCompetenza=<?php echo urlencode($competenza['Competenza']); ?>" class="btn btn-danger btn-sm float-end ml-2">Rimuovi</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </main>

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
