<?php
include_once 'auth.php';
include_once 'connection.php';
include_once 'navbar.php';

// Connessioni ai database
$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();

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

    <!-- Progetti Attivi -->
    <section class="mb-5">
      <h2 class="section-title">Progetti Attivi</h2>

      <div class="row">
        <?php
        $maxDisplay = 3;
        $totalProgetti = count($progetti);

        for ($i = 0; $i < min($maxDisplay, $totalProgetti); $i++) {
            $row = $progetti[$i];
            $nomeProgettoUrl = urlencode($row["Nome"]);
        ?>
          <div class="col-md-4 mb-4">
            <div class="card card-custom h-100">
              <div class="card-body">
                <h5 class="card-title">
                  <a href="progetto.php?nome=<?= $nomeProgettoUrl ?>" class="text-decoration-none text-primary"><?= htmlspecialchars($row["Nome"]) ?></a>
                </h5>
                <p class="card-text"><?= htmlspecialchars($row["Descrizione"]) ?></p>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>

      <?php if ($totalProgetti > 3): ?>
        <div class="text-center mt-4">
          <a href="visualizzaProgetti.php" class="btn btn-secondary">Visualizza altri</a>
        </div>
      <?php endif; ?>
    </section>

    <!-- Classifica Creatori -->
    <section class="mb-5">
      <h2 class="section-title">Classifica Creatori</h2>
      <ol class="list-group list-group-numbered list-group-custom">
        <li class="list-group-item">Noe</li>
        <li class="list-group-item">Ale</li>
      </ol>
    </section>

    <!-- Progetti Quasi Finiti -->
    <section class="mb-5">
      <h2 class="section-title">Progetti Quasi Finiti</h2>
      <ol class="list-group list-group-numbered list-group-custom">
        <li class="list-group-item">Basi di dati</li>
        <li class="list-group-item">Ingegneria</li>
      </ol>
    </section>

    <!-- Classifica Utenti -->
    <section class="mb-5">
      <h2 class="section-title">Classifica Utenti</h2>
      <ol class="list-group list-group-numbered list-group-custom">
        <li class="list-group-item">Leo</li>
        <li class="list-group-item">Marco</li>
      </ol>
    </section>

  </div>
</main>

<?php include_once 'footer.php'; ?>

</body>
</html>
