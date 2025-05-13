<?php
include_once 'auth.php';
include_once 'connection.php';
include_once 'navbar.php';

// Connessioni ai database
$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

$emailCreatore = $_SESSION['id'];

// Query per recuperare i progetti del creatore loggato
try {
  $sql = "SELECT * FROM PROGETTO WHERE Email_Creatore = :emailCreatore";
  $stmt = $mysqlConn->prepare($sql);
  $stmt->execute(['emailCreatore' => $emailCreatore]);
  $progetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Errore durante il caricamento dei progetti: " . $e->getMessage());
}
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

<header class="hero">
    <div class="container text-center py-5">
      <h1 class="display-4 text-white mb-4 animate-fadein">Area creatore</h1>
      <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Gestisci i tuoi progetti!</p>
    </div>
  </header>

<main>
  <div class="container">

    <!-- Messaggio di errore -->
    <?php if (!empty($errorMessage)): ?>
      <div class="alert alert-danger mt-3" role="alert">
        <?= htmlspecialchars($errorMessage) ?>
      </div>
    <?php endif; ?>

    <!-- Bottone nuovo progetto -->
    <div class="text-center mb-5">
      <a href="nuovoProgetto.php" class="btn btn-primary">Inserisci Nuovo Progetto</a>
    </div>

    <!-- Progetti del creatore -->
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
                    <span> <?= htmlspecialchars($row["Budget"]) ?> </span>
                    <span><?= htmlspecialchars($row["Data_Limite"]) ?> </span>
                  </div>
                  <div>
                    <span> <?= htmlspecialchars($row["Tipo"]) ?> <span>
                  </div>
                  <div>
                    <span> <?= htmlspecialchars($row["Stato"]) ?> <span>
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
          Non hai ancora creato progetti.
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php include_once 'footer.php'; ?>

</body>
</html>
