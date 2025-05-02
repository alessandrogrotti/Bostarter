<?php
include_once 'auth.php';   // definisce requireLogin(), requireCreator()
requireLogin();

include_once 'connection.php';
include_once 'navbar.php';
include_once 'mongodb.php';

$mysqlConn     = getMySQLConnection();
$logCollection = getMongoDBConnection();
writeLog('Visita pagina inserimento progetto', 'Accesso alla pagina');

$message = '';
$uploadDir = 'uploads/'; // Directory di destinazione immagini

// Crea la cartella uploads/ se non esiste
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Acquisizione e sanitizzazione input
    $nome        = trim($_POST['nome']);
    $descrizione = trim($_POST['descrizione']);
    $budget      = floatval($_POST['budget']);
    $dataLimite  = $_POST['dataLimite'];

    $software = isset($_POST['software']) && $_POST['software'] == 1;
    $hardware = isset($_POST['hardware']) && $_POST['hardware'] == 1;

    // 2) emailCreatore da sessione
    $emailCreatore = $_SESSION['id'];

    // 3) definizione di $tipo
    if ($software && !$hardware) {
        $tipo = 'Software';
    } elseif ($hardware && !$software) {
        $tipo = 'Hardware';
    } else {
        die('Errore: seleziona esattamente una tipologia (Software O Hardware).');
    }

    try {
        // 4) Stored procedure per inserimento progetto
        $stmt = $mysqlConn->prepare("
            CALL InserisciProgetto(
                :inNome,
                :inEmailCreatore,
                :inDescrizione,
                :inDataLimite,
                :inBudget,
                :inTipo
            )
        ");
        $stmt->bindParam(':inNome',          $nome,          PDO::PARAM_STR);
        $stmt->bindParam(':inEmailCreatore', $emailCreatore, PDO::PARAM_STR);
        $stmt->bindParam(':inDescrizione',   $descrizione,   PDO::PARAM_STR);
        $stmt->bindParam(':inDataLimite',    $dataLimite,    PDO::PARAM_STR);
        $stmt->bindParam(':inBudget',        $budget);
        $stmt->bindParam(':inTipo',          $tipo,          PDO::PARAM_STR);
        $stmt->execute();

        // 5) Verifica inserimento progetto
        if ($stmt->rowCount() > 0) {
            writeLog('Inserimento progetto', "Progetto \"$nome\" inserito da $emailCreatore");

            // 6) Gestione upload immagini se esistono
            if (isset($_FILES['foto']) && count($_FILES['foto']['name']) > 0) {
                foreach ($_FILES['foto']['tmp_name'] as $index => $tmpName) {
                    if ($_FILES['foto']['error'][$index] === UPLOAD_ERR_OK) {
                        $originalName = basename($_FILES['foto']['name'][$index]);
                        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                        // Accetta solo immagini
                        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $newFileName = uniqid('img_', true) . '.' . $extension;
                            $destinationPath = $uploadDir . $newFileName;

                            // Salva fisicamente il file
                            if (move_uploaded_file($tmpName, $destinationPath)) {
                                // Inserisci il percorso nel database FOTO
                                $insertFoto = $mysqlConn->prepare("INSERT INTO FOTO (Valore, Nome_Progetto) VALUES (:valore, :nomeProgetto)");
                                $insertFoto->bindParam(':valore', $destinationPath, PDO::PARAM_STR);
                                $insertFoto->bindParam(':nomeProgetto', $nome, PDO::PARAM_STR);
                                $insertFoto->execute();
                            }
                        }
                    }
                }
            }

            $message = 'Progetto inserito con successo!';
        } else {
            $message = 'Attenzione: nessun progetto è stato inserito. Controlla i dati.';
        }
    } catch (PDOException $e) {
        die("Errore durante l'inserimento: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inserisci Progetto | Bostarter</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container py-5">
    <h1 class="mb-4">Nuovo Progetto</h1>

    <?php if ($message): ?>
      <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="row g-3" enctype="multipart/form-data">
      <div class="col-md-6">
        <label for="nome" class="form-label">Nome</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
      </div>
      <div class="col-md-6">
        <label for="descrizione" class="form-label">Descrizione</label>
        <input type="text" class="form-control" id="descrizione" name="descrizione" required>
      </div>
      <div class="col-md-4">
        <label for="budget" class="form-label">Budget</label>
        <input type="text" class="form-control" id="budget" name="budget" required>
      </div>
      <div class="col-md-4">
        <label for="dataLimite" class="form-label">Data Limite</label>
        <input type="date" class="form-control" id="dataLimite" name="dataLimite" required>
      </div>
      <div class="col-md-4 d-flex align-items-center">
        <div class="form-check me-3">
          <input class="form-check-input" type="checkbox" value="1" id="software" name="software">
          <label class="form-check-label" for="software">Software</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="1" id="hardware" name="hardware">
          <label class="form-check-label" for="hardware">Hardware</label>
        </div>
      </div>

      <div class="col-12">
        <label for="foto" class="form-label">Carica Foto</label>
        <input class="form-control" type="file" id="foto" name="foto[]" multiple accept="image/*">
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Inserisci</button>
      </div>
    </form>
  </main>

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
