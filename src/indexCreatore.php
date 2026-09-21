<?php
include_once 'auth.php';
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'mysql.php';
include_once 'navbar.php';

requireCreator();

$emailCreatore = $_SESSION['id'];

writeLog('Visita area creatore', ['email' => $emailCreatore]);

$_SESSION['error'] = '';

try {
    $progetti = ottieniProgettiCreatore($emailCreatore);

    foreach ($progetti as &$progetto) {
        $progetto['Foto'] = ottieniFotoProgetto($progetto['Nome']);
    }
    unset($progetto);
} catch (Exception $e) {
    writeLog('Errore caricamento progetti creatore', ['email' => $emailCreatore, 'errore' => $e->getMessage()]);
    $_SESSION['error'] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Area creatore | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<header class="hero">
  <div class="container text-center py-5">
    <h1 class="display-4 text-white mb-4 animate-fadein">Area creatore</h1>
    <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Gestisci i tuoi progetti</p>
  </div>
</header>

<main>
  <div class="container">
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

    <div class="text-center mb-5" style="margin-top: 50px;">
      <a href="nuovoProgetto.php" class="btn btn-primary">Crea Nuovo Progetto</a>
    </div>

    <section class="mb-5">
      <h2 class="section-title">Tutti i progetti</h2>

      <?php if (count($progetti) > 0): ?>
        <div class="row g-4">
          <?php foreach ($progetti as $index => $row): ?>
            <?php $nomeProgettoUrl = urlencode($row["Nome"]); ?>
            <div class="col-lg-4 col-md-6">
              <div class="card h-100">
                <?php if (!empty($row['Foto'])): ?>
                    <div id="carousel-<?= $index ?>" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($row['Foto'] as $i => $foto): ?>
                                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>"> 
                                   <img src="<?= htmlspecialchars($foto) ?>" class="d-block w-100" alt="Foto progetto" style="height: 200px; object-fit: cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($row['Foto']) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $index ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Precedente</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $index ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Successiva</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <img class="card-img-top" style="height: 200px; object-fit: cover">
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
                    <p class="card-text text-muted mb-4"><?= htmlspecialchars($row["Descrizione"]) ?> </p>

                    <hr class="my-4 border-top border-4" />

                    <div class="d-flex justify-content-between text-muted small">
                        <span> <?= htmlspecialchars($row["Budget"]) ?> </span>
                        <span> <?= htmlspecialchars($row["Data_Limite"]) ?> </span>
                    </div>

                    <div>
                        <span> <?= htmlspecialchars($row["Tipo"]) ?> </span>
                    </div>

                    <div>
                        <span> <?= htmlspecialchars($row["Stato"]) ?> </span>
                    </div>  
                </div>

                <div class="card-footer bg-transparent border-0">
                    <a href="progettoCreatore.php?nome=<?= $nomeProgettoUrl ?>" class="btn btn-primary w-100">Modifica progetto</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-warning text-center">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          Non hai ancora creato progetti.
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php include_once 'footer.php'; ?>

</body>
</html>
