<?php
include_once 'connection.php';
include_once 'mongodb.php';

$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

// Log visita homepage
writeLog('Visita homepage', 'Accesso alla homepage da parte di un utente');

try {
    $stmt = $mysqlConn->query("CALL GetAvailableProjects()");
    $progetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore durante l'esecuzione della stored procedure: " . $e->getMessage());
}
?>
<?php include_once 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Homepage | Bostarter</title>
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
      <h1 class="display-4 text-white mb-4 animate-fadein">Benvenuto su Bostarter</h1>
      <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Scopri i progetti più innovativi e sostieni le idee che ti appassionano</p>
    </div>
  </header>

  <main class="container my-5">
    <!-- Sezione Progetti Attivi -->
    <section class="mb-5 animate-fadein" style="animation-delay: 0.4s">
      <h2 class="section-title">Progetti Attivi</h2>
      <div class="row g-4">
        <?php
        $maxDisplay = 3;
        $totalProgetti = count($progetti);

        for ($i = 0; $i < min($maxDisplay, $totalProgetti); $i++) {
            $row = $progetti[$i];
            $nomeProgettoUrl = urlencode($row["Nome"]);
            ?>
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
            <?php
        }
        ?>
      </div>

      <?php if ($totalProgetti > 3): ?>
        <div class="text-center mt-4">
          <a href="visualizzaProgetti.php" class="btn btn-outline-primary">Visualizza tutti i progetti</a>
        </div>
      <?php endif; ?>
    </section>

    <!-- Classifiche -->
    <div class="row g-4 animate-fadein" style="animation-delay: 0.6s">
      <!-- Classifica Creatori -->
      <div class="col-lg-4 col-md-6">
        <section class="p-4 bg-light rounded-lg">
          <h3 class="h4 mb-4">Classifica Creatori</h3>
          <ol class="list-group list-group-numbered">
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">Noe</div>
                <span class="text-muted small">5 progetti</span>
              </div>
              <span class="badge bg-accent rounded-pill">1°</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">Ale</div>
                <span class="text-muted small">3 progetti</span>
              </div>
              <span class="badge bg-primary rounded-pill">2°</span>
            </li>
          </ol>
        </section>
      </div>

      <!-- Progetti Quasi Finiti -->
      <div class="col-lg-4 col-md-6">
        <section class="p-4 bg-light rounded-lg">
          <h3 class="h4 mb-4">Progetti Quasi Finiti</h3>
          <ol class="list-group list-group-numbered">
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">Basi di dati</div>
                <span class="text-muted small">95% completato</span>
              </div>
              <span class="badge bg-accent rounded-pill">€1,250</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">Ingegneria</div>
                <span class="text-muted small">89% completato</span>
              </div>
              <span class="badge bg-primary rounded-pill">€980</span>
            </li>
          </ol>
        </section>
      </div>

      <!-- Classifica Utenti -->
      <div class="col-lg-4 col-md-6">
        <section class="p-4 bg-light rounded-lg">
          <h3 class="h4 mb-4">Classifica Utenti</h3>
          <ol class="list-group list-group-numbered">
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">Leo</div>
                <span class="text-muted small">12 contributi</span>
              </div>
              <span class="badge bg-accent rounded-pill">Top</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold">Marco</div>
                <span class="text-muted small">8 contributi</span>
              </div>
              <span class="badge bg-primary rounded-pill">2°</span>
            </li>
          </ol>
        </section>
      </div>
    </div>
  </main>

  <?php include_once 'footer.php'; ?>
</body>
</html>