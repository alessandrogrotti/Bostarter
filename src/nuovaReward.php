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
    && isset($_POST['codice'], $_POST['descrizione'], $_POST['foto'])
) {
    $codice      = trim($_POST['codice']);
    $descrizione = trim($_POST['descrizione']);
    $foto        = trim($_POST['foto']);  // se vuoi un file upload, cambia qui

    if ($codice === '' || $descrizione === '' ) {
        $errorMessage = "Compila tutti i campi obbligatori.";
    } else {
        try {
            $stmtI = $conn->prepare(
                "CALL InsertReward(
                    :p_Codice,
                    :p_Descrizione,
                    :p_Foto,
                    :p_Nome_Progetto
                )"
            );
            $stmtI->bindParam(':p_Codice',         $codice,       PDO::PARAM_STR);
            $stmtI->bindParam(':p_Descrizione',    $descrizione,  PDO::PARAM_STR);
            $stmtI->bindParam(':p_Foto',           $foto,         PDO::PARAM_STR);
            $stmtI->bindParam(':p_Nome_Progetto',  $nomeProgetto, PDO::PARAM_STR);
            $stmtI->execute();
            $stmtI->closeCursor();

            // log su MongoDB
            writeLog(
                'Inserimento reward',
                "Creatore {$_SESSION['id']} ha aggiunto reward '$codice' al progetto $nomeProgetto"
            );

            // redirect
            header("Location: progetto.php?nome=" . urlencode($nomeProgetto));
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container mt-5">

    <h3 class="mb-4">Aggiungi nuova reward per "<strong><?= htmlspecialchars($nomeProgetto) ?></strong>"</h3>

    <?php if (!empty($errorMessage)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-5">
      <div class="mb-3">
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
      <div class="mb-3">
        <label for="descrizione" class="form-label">Descrizione:</label>
        <textarea
          id="descrizione"
          name="descrizione"
          class="form-control"
          rows="4"
          required
        ><?= isset($descrizione) ? htmlspecialchars($descrizione) : '' ?></textarea>
      </div>
      <div class="mb-3">
        <label for="foto" class="form-label">URL o percorso immagine (opzionale):</label>
        <input
          type="text"
          id="foto"
          name="foto"
          class="form-control"
          maxlength="255"
          value="<?= isset($foto) ? htmlspecialchars($foto) : '' ?>"
        >
      </div>
      <button type="submit" class="btn btn-primary">Salva reward</button>

    </form>

  </main>

  <footer class="text-center mt-5 py-3 bg-light">
    <p>Progetto Bostarter &copy; <?= date('Y') ?></p>
  </footer>
</body>
</html>
