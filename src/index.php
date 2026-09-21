<?php
include_once 'auth.php';
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'mysql.php';
include_once 'navbar.php';

writeLog('Visita homepage', 'Accesso alla homepage da parte di un utente');

try {
    $progetti = ottieniProgettiDisponibili();

    foreach ($progetti as &$progetto) {
        try {
            $progetto['Foto'] = ottieniFotoProgetto($progetto['Nome']);
        } catch (Exception $e) {
            writeLog('Errore caricamento foto progetto', ['errore' => $e->getMessage()]);
            $_SESSION['error'] = $e->getMessage();
        }
    }
    unset($progetto);
} catch (Exception $e) {
    writeLog('Errore caricamento homepage', ['errore' => $e->getMessage()]);
    $_SESSION['error'] = $e->getMessage();
}

try {
    $vistaAffidabilità = OttieniListaAffidabilità();
} catch (Exception $e) {
    writeLog('Errore caricamento classifica affidabilità', ['errore' => $e->getMessage()]);
    $_SESSION['error'] = $e->getMessage();
}

try {
    $vistaProgetti = OttieniListaProgetti();
} catch (Exception $e) {
    writeLog('Errore caricamento classifica progetti', ['errore' => $e->getMessage()]);
    $_SESSION['error'] = $e->getMessage();
}

try {
    $vistaFinanziatori = OttieniListaFinanziatori();
} catch (Exception $e) {
    writeLog('Errore caricamento classifica finanziatori', ['errore' => $e->getMessage()]);
    $_SESSION['error'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Homepage | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<header class="hero">
  <div class="container text-center py-5">
    <h1 class="display-4 text-white mb-4 animate-fadein">Benvenuto su Bostarter</h1>
    <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Scopri i progetti più innovativi e sostieni le idee che ti appassionano</p>
  </div>
</header>

<?php if ($_SESSION['error']): ?>
  <div class="alert alert-danger mt-4">
    <?= htmlspecialchars($_SESSION['error']) ?>
  </div>
  <?php $_SESSION['error'] = ''; ?>
<?php endif; ?>
<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success mt-4">
    <?= htmlspecialchars($_SESSION['success']) ?>
  </div>
  <?php $_SESSION['success'] = ''; ?>
<?php endif; ?>

<main class="container my-5">
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
            <div class="card h-100">
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
                <img class="card-img-top" style="object-fit: cover; height: 200px;">
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
                <a href="progetto.php?nome=<?= urlencode($row["Nome"]) ?>" class="btn btn-primary w-100">Partecipa al progetto</a>
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

  <div class="row g-4 animate-fadein" style="animation-delay: 0.6s">
    <div class="col-lg-4 col-md-6">
      <section class="p-4 bg-light rounded-lg">
        <h3 class="h4 mb-4">Classifica Creatori</h3>
        <ol class="list-group list-group-numbered">
          <?php for ($i = 0; $i < min(3, count($vistaAffidabilità)); $i++): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold"><?= htmlspecialchars($vistaAffidabilità[$i]['Nickname']) ?></div>
                <span> Affidabilità: <?= number_format($vistaAffidabilità[$i]['Affidabilità'] * 100, 0) ?>% </span>
              </div>
            </li>
          <?php endfor; ?>
        </ol>
      </section>
    </div>

    <div class="col-lg-4 col-md-6">
      <section class="p-4 bg-light rounded-lg">
        <h3 class="h4 mb-4">Progetti Quasi Finiti</h3>
        <ol class="list-group list-group-numbered">
          <?php for ($i = 0; $i < min(3, count($vistaProgetti)); $i++): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold"><?= htmlspecialchars($vistaProgetti[$i]['Nome']) ?></div>
                <span><?= number_format($vistaProgetti[$i]['Differenza'], 0) ?>€ al completamento</span>
              </div>
            </li>
          <?php endfor; ?>
        </ol>
      </section>
    </div>

    <div class="col-lg-4 col-md-6">
      <section class="p-4 bg-light rounded-lg">
        <h3 class="h4 mb-4">Classifica Utenti</h3>
        <ol class="list-group list-group-numbered">
          <?php for ($i = 0; $i < min(3, count($vistaFinanziatori)); $i++): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="ms-2 me-auto">
                <div class="fw-bold"><?= htmlspecialchars($vistaFinanziatori[$i]['Nickname']) ?></div>
                <span>Finanziati: <?= number_format($vistaFinanziatori[$i]['Totale'], 1) ?>€</span>
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
