<?php
include_once 'auth.php';
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'mysql.php';

$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

// Log visita homepage
writeLog('Visita homepage', 'Accesso alla homepage da parte di un utente');

try {
  $stmt = $mysqlConn->query("CALL GetAvailableProjects()");
  $progetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor(); // CHIUDI IL CURSORE QUI! 🔥

  // Ora puoi eseguire altre query senza errori
  foreach ($progetti as &$progetto) {
      $nomeProgetto = $progetto['Nome'];
      $fotoStmt = $mysqlConn->prepare("SELECT Valore FROM FOTO WHERE Nome_Progetto = :nomeProgetto");
      $fotoStmt->bindParam(':nomeProgetto', $nomeProgetto, PDO::PARAM_STR);
      $fotoStmt->execute();
      $progetto['Foto'] = $fotoStmt->fetchAll(PDO::FETCH_COLUMN);
  }
  unset($progetto);
} catch (PDOException $e) {
  die("Errore durante l'esecuzione della stored procedure: " . $e->getMessage());
}

$vistaAffidabilità = OttieniListaAffidabilità();
$vistaProgetti = OttieniListaProgetti();
$vistaFinanziatori = OttieniListaFinanziatori();

include_once 'navbar.php'; 

?>
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
      $percentuale = isset($row["Completamento"]) ? intval($row["Completamento"]) : 25; // fallback
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="card shadow-hover h-100">
        <?php if (!empty($row['Foto'])): ?>
            <div id="carousel-<?= $i ?>" class="carousel slide" data-bs-ride="carousel">
              <div class="carousel-inner">
                <?php foreach ($row['Foto'] as $index => $fotoUrl): ?>
                  <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($fotoUrl) ?>" class="d-block w-100" alt="Foto Progetto" style="object-fit: cover; height: 200px;">
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if (count($row['Foto']) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $i ?>" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Precedente</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $i ?>" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Successiva</span>
                </button>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <img src="placeholder.jpg" class="card-img-top" alt="Nessuna immagine" style="object-fit: cover; height: 200px;">
          <?php endif; ?>

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

            <hr class="my-4 border-top border-4" />

            <div class="d-flex justify-content-between text-muted small">
              <span><?= htmlspecialchars($row["Budget"]) ?></span>
              <span><?= htmlspecialchars($row["Data_Limite"]) ?></span>
            </div>
            <div class="text-muted small mt-2">
              <span><?= htmlspecialchars($row["Tipo"]) ?></span> | 
              <span><?= htmlspecialchars($row["Stato"]) ?></span>
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
          <?php for ($i = 0; $i < min(3, count($vistaAffidabilità)); $i++): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold"><?= htmlspecialchars($vistaAffidabilità[$i]['Nickname']) ?></div>
                <span> Affidabilità: <?= htmlspecialchars($vistaAffidabilità[$i]['Affidabilità']) ?> </span>
              </div>
            </li>
          <?php endfor; ?>
        </ol>
      </section>
    </div>

    <!-- Progetti Quasi Finiti -->
    <div class="col-lg-4 col-md-6">
      <section class="p-4 bg-light rounded-lg">
        <h3 class="h4 mb-4">Progetti Quasi Finiti</h3>
        <ol class="list-group list-group-numbered">
          <?php for ($i = 0; $i < min(3, count($vistaProgetti)); $i++): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold"><?= htmlspecialchars($vistaProgetti[$i]['Nome']) ?></div>
                <span><?= htmlspecialchars($vistaProgetti[$i]['Differenza']) ?>€ al completamento</span>
              </div>
            </li>
          <?php endfor; ?>
        </ol>
      </section>
    </div>

    <!-- Classifica Utenti -->
    <div class="col-lg-4 col-md-6">
      <section class="p-4 bg-light rounded-lg">
        <h3 class="h4 mb-4">Classifica Utenti</h3>
        <ol class="list-group list-group-numbered">
          <?php for ($i = 0; $i < min(3, count($vistaFinanziatori)); $i++): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold"><?= htmlspecialchars($vistaFinanziatori[$i]['Nickname']) ?></div>
                <span>Finanziati: <?= htmlspecialchars($vistaFinanziatori[$i]['Totale']) ?>€</span>
              </div>
            </li>
          <?php endfor; ?>
        </ol>
      </section>
    </div>

  </div>
</main>

<?php include_once 'footer.php'; ?>
</body>
</html>
