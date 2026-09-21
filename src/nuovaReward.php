<?php
include_once 'connection.php';
include_once 'mongodb.php';
include_once 'auth.php';
include_once 'mysql.php';

requireLogin();

$nomeProgetto = isset($_GET['nome']) ? trim($_GET['nome']) : '';
if ($nomeProgetto === '') {
    $_SESSION['error'] = "Nome progetto non specificato.";
    header("Location: indexCreatore.php");
    exit;
}

if (!verificaProgettoCreatore($nomeProgetto, $_SESSION['id'])) {
    $_SESSION['error'] = "Progetto non trovato o non autorizzato.";
    header("Location: indexCreatore.php");
    exit;
}

$_SESSION['error'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descrizione'])) {
    $descrizione = trim($_POST['descrizione']);
    $fotoPath = '';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = basename($_FILES['foto']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $newFileName = uniqid('reward_', true) . '.' . $extension;
            $destinationPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destinationPath)) {
                $_SESSION['error'] = "Errore durante il salvataggio dell'immagine.";
            } else {
                $fotoPath = $destinationPath;
            }
        } else {
            $_SESSION['error'] = "Formato immagine non valido.";
        }
    }

    if ($descrizione === '') {
        $_SESSION['error'] = "Compila tutti i campi obbligatori.";
    } else {
        try {
            inserisciReward($descrizione, $fotoPath, $nomeProgetto, $_SESSION['id']);
            writeLog(
                'Inserimento reward',
                "Creatore {$_SESSION['id']} ha aggiunto reward al progetto $nomeProgetto"
            );
            header("Location: progettoCreatore.php?nome=" . urlencode($nomeProgetto));
            exit;
        } catch (Exception $e) {
            writeLog('Errore inserimento reward', ['errore' => $e->getMessage()]);
            $_SESSION['error'] = "Errore inserimento reward: " . $e->getMessage();
        }
    }
}

if (!empty($_SESSION['error'])) {
    header("Location: indexCreatore.php");
    exit;
}

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

    <form method="POST" class="row g-3" enctype="multipart/form-data">
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
