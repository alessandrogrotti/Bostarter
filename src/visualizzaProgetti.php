<?php
include_once 'connection.php';
include_once 'navbar.php';
include_once 'mongodb.php';

$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

writeLog('Visita pagina progetti', 'Accesso alla pagina dei progetti da parte di un utente');

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
  <title>Progetti | Bostarter</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <!-- Hero Section -->
  <header class="hero">
    <div class="container text-center py-5">
      <h1 class="display-4 text-white mb-4">Scopri tutti i progetti</h1>
      <p class="lead text-white-50">Sostieni le idee che ti appassionano e contribuisci al loro successo</p>
    </div>
  </header>

  <main class="container my-5">
    <!-- Filtri e Ricerca -->
    <div class="row mb-5">
      <div class="col-md-6 mb-3">
        <div class="input-group">
          <input type="text" class="form-control" placeholder="Cerca progetti...">
          <button class="btn btn-primary" type="button">Cerca</button>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <select class="form-select">
          <option selected>Ordina per</option>
          <option>Più recenti</option>
          <option>Più popolari</option>
          <option>Scadenza più vicina</option>
        </select>
      </div>
    </div>

    <!-- Sezione Progetti -->
    <section class="mb-5">
      <h2 class="section-title">Tutti i progetti disponibili</h2>
      
      <?php if (count($progetti) > 0): ?>
        <div class="row g-4">
          <?php foreach ($progetti as $row): ?>
            <?php $nomeProgettoUrl = urlencode($row["Nome"]); ?>
            <div class="col-lg-4 col-md-6">
              <div class="card shadow-hover h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">
                      <a href="progetto.php?nome=<?= $nomeProgettoUrl ?>" class="text-decoration-none">
                        <?= htmlspecialchars($row["Nome"]) ?>
                      </a>
                    </h5>
                    <span class="badge bg-accent">Nuovo</span>
                  </div>
                  <p class="card-text text-muted mb-4"><?= htmlspecialchars($row["Descrizione"]) ?></p>
                  <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-accent" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <div class="d-flex justify-content-between text-muted small">
                    <span>25% completato</span>
                    <span>15 giorni rimasti</span>
                  </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                  <a href="progetto.php?nome=<?= $nomeProgettoUrl ?>" class="btn btn-primary w-100">Partecipa al progetto</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-warning text-center">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          Non sono presenti progetti attivi al momento.
        </div>
      <?php endif; ?>
    </section>

    <!-- Paginazione -->
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <li class="page-item disabled">
          <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Precedente</a>
        </li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item">
          <a class="page-link" href="#">Successivo</a>
        </li>
      </ul>
    </nav>
  </main>

  <footer class="footer py-5 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h5 class="text-white">Bostarter</h5>
          <p class="text-white-50">La piattaforma che sostiene le idee innovative e le fa crescere.</p>
        </div>
        <div class="col-md-3">
          <h5 class="text-white">Link utili</h5>
          <ul class="list-unstyled footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="visualizzaProgetti.php">Progetti</a></li>
            <li><a href="#">Come funziona</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h5 class="text-white">Contatti</h5>
          <ul class="list-unstyled footer-links">
            <li><a href="mailto:info@bostarter.it">info@bostarter.it</a></li>
            <li><a href="#">Termini e condizioni</a></li>
          </ul>
        </div>
      </div>
      <hr class="my-4 bg-white-10">
      <div class="text-center">
        <p class="text-white-50 mb-0">&copy; 2025 Bostarter. Tutti i diritti riservati.</p>
      </div>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>