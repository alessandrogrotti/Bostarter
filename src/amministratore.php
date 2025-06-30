<?php
include_once 'auth.php';
include_once 'navbar.php';
include_once 'mongodb.php';
include_once 'mysql.php';

requireAdmin();

writeLog('Visita pagina competenza', 'Accesso alla pagina per aggiunta competenza da parte dell’amministratore');

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['competenza'])) {
    $nomeCompetenza = $_POST['competenza'];
    try {
        if (inserisciCompetenza($nomeCompetenza)) {
            writeLog('Inserimento competenza', "Competenza '$nomeCompetenza' aggiunta al database.");
            $errorMessage = "Competenza '$nomeCompetenza' aggiunta con successo.";
        }
    } catch (Exception $e) {
        writeLog('Errore inserimento competenza', ['errore' => $e->getMessage()]);
        $errorMessage = $e->getMessage();
    }
}

if (isset($_GET['removeCompetenza'])) {
    $competenzaToRemove = $_GET['removeCompetenza'];
    try {
        if (eliminaCompetenza($competenzaToRemove)) {
            writeLog('Eliminazione competenza', "Competenza '$competenzaToRemove' eliminata dal database.");
            $errorMessage = "Competenza '$competenzaToRemove' eliminata con successo.";
        }
    } catch (Exception $e) {
        writeLog('Errore eliminazione competenza', ['errore' => $e->getMessage()]);
        $errorMessage = $e->getMessage();
    }
}

try {
    $competenze = ottieniCompetenze();
} catch (Exception $e) {
    writeLog('Errore caricamento competenze', ['errore' => $e->getMessage()]);
    $errorMessage = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Amministratore - Aggiunta Competenza</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="hero">
  <div class="container text-center py-5">
    <h1 class="display-4 text-white mb-4 animate-fadein">Gestione Competenze</h1>
    <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s;">Aggiungi o rimuovi competenze disponibili nel sistema</p>
  </div>
</header>

<?php if ($errorMessage): ?>
  <div class="alert alert-danger mb-4">
    <?= htmlspecialchars($errorMessage) ?>
  </div>      
<?php endif; ?>

<main class="container">

  <section class="mb-5" style="margin-top: 50px;">
    <h2 class="section-title">Aggiungi una competenza</h2>
    <div class="card card-custom p-4">
      <form method="POST" action="amministratore.php">
        <div class="mb-3">
          <label for="competenza" class="form-label">Nome competenza:</label>
          <input type="text" id="competenza" name="competenza" class="form-control" required />
        </div>
        <div class="text-center">
          <button type="submit" class="btn btn-primary">Inserisci</button>
        </div>
      </form>
    </div>
  </section>

  <section class="mb-5">
    <h2 class="section-title">Lista delle competenze</h2>
    <ul class="list-group list-group-flush list-group-custom">
      <?php foreach ($competenze as $competenza): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <?= htmlspecialchars($competenza['Competenza']); ?>
          <a href="?removeCompetenza=<?= urlencode($competenza['Competenza']); ?>" class="btn btn-danger btn-sm">Rimuovi</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

</main>

<footer class="text-center mt-5 text-muted">
  <p>Progetto Bostarter &copy; 2025</p>
</footer>

</body>
</html>
