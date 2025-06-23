<?php
include_once 'connection.php';
include_once 'mongodb.php';      
include_once 'auth.php';         

$conn          = getMySQLConnection();
$logCollection = getMongoDBConnection();

// 2) Verifica che l’utente sia loggato
requireLogin();

// 3) prendi il nome del progetto da GET e controlla che esista
$nomeProgetto = isset($_GET['nome']) ? trim($_GET['nome']) : '';
if ($nomeProgetto === '') {
    die("Nome progetto non specificato.");
}
try {
    $stmt = $conn->prepare(
        "SELECT Email_Creatore
         FROM PROGETTO
         WHERE Nome = :nome"
    );
    $stmt->bindParam(':nome', $nomeProgetto, PDO::PARAM_STR);
    $stmt->execute();
    $progetto = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    die("Errore recupero progetto: " . $e->getMessage());
}
if (! $progetto) {
    die("Progetto non trovato.");
}

// 5) gestione form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['codice'], $_POST['descrizione'])
) {
    $codice      = trim($_POST['codice']);
    $descrizione = trim($_POST['descrizione']);
    $fotoPath    = ''; // Variabile per il percorso della foto

    // Gestione dell'upload dell'immagine
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['foto']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Verifica l'estensione del file (solo immagini)
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $newFileName = uniqid('reward_', true) . '.' . $extension;
            $destinationPath = $uploadDir . $newFileName;

            // Salvataggio dell'immagine
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destinationPath)) {
                $fotoPath = $destinationPath; // Salvo il percorso della foto
            } else {
                $errorMessage = "Errore durante il salvataggio dell'immagine.";
            }
        } else {
            $errorMessage = "Formato immagine non valido.";
        }
    }

    // Controlla che i campi obbligatori siano compilati
    if ($codice === '' || $descrizione === '') {
        $errorMessage = "Compila tutti i campi obbligatori.";
    } else {
        // Prendi l'email dell'utente loggato
        $emailCreatore = $_SESSION['id'];  // oppure modifica se usi un altro nome per l'email nella sessione

        try {
            // Esegui la chiamata alla stored procedure
            $stmtI = $conn->prepare(
                "CALL InserisciReward(
                    :codice,
                    :descrizione,
                    :foto,
                    :nome_progetto,
                    :email_creatore
                )"
            );
            $stmtI->bindParam(':codice',         $codice,        PDO::PARAM_STR);
            $stmtI->bindParam(':descrizione',    $descrizione,   PDO::PARAM_STR);
            $stmtI->bindParam(':foto',           $fotoPath,      PDO::PARAM_STR);  // Passa il percorso della foto
            $stmtI->bindParam(':nome_progetto',  $nomeProgetto,  PDO::PARAM_STR);
            $stmtI->bindParam(':email_creatore', $emailCreatore, PDO::PARAM_STR);
            $stmtI->execute();
            $stmtI->closeCursor();

            // Log su MongoDB
            writeLog(
                'Inserimento reward',
                "Creatore $emailCreatore ha aggiunto reward '$codice' al progetto $nomeProgetto"
            );

            // Redirect alla pagina del progetto
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        } catch (PDOException $e) {
            $errorMessage = "Errore inserimento reward: " . $e->getMessage();
        }
    }
}

// 6) include navbar (dopo eventuale redirect)
include_once 'navbar.php';

?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Nuova Reward | Bostarter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Libre+Baskerville:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container mt-5">

    <h2 class="mb-4">Nuova reward per "<strong><?= htmlspecialchars($nomeProgetto) ?></strong>"</h2>

    <?php if (!empty($errorMessage)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3" enctype="multipart/form-data">
      <div class="col-md-6">
        <label for="codice" class="form-label">Codice reward:</label>
        <input
          type="text"
          id="codice"
          name="codice"
          class="form-control"
          maxlength="50"
          required
          value="<?= isset($codice) ? htmlspecialchars($codice) : '' ?>"
        >
      </div>
      <div class="col-12">
        <label for="descrizione" class="form-label">Descrizione:</label>
        <textarea
          id="descrizione"
          name="descrizione"
          class="form-control"
          rows="4"
          required
        ><?= isset($descrizione) ? htmlspecialchars($descrizione) : '' ?></textarea>
      </div>
      <div class="col-12">
        <label for="foto" class="form-label">Carica immagine:</label>
        <input
          type="file"
          id="foto"
          name="foto"
          class="form-control"
          accept="image/*"
        >
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Salva Reward</button>
      </div>
    </form>
  </main>

  <footer class="text-center mt-5 py-3 bg-light">
    <p>Bostarter &copy; <?= date('Y') ?></p>
  </footer>
</body>
</html>
