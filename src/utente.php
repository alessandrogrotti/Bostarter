<?php
include_once 'auth.php';

include_once 'connection.php';
include_once 'mongodb.php';

$mysqlConn = getMySQLConnection();
$logCollection = getMongoDBConnection();
$email = $_SESSION['id'];
$message = "";

$userData = [];
$userSkills = [];
$availableSkills = [];

try {
    // Recupera dati utente
    $stmt = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        $message = "Utente non trovato.";
    } else {
        writeLog("Visualizzazione utente con skill", ["email" => $email]);
    }

    // Aggiunta skill
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skill'], $_POST['level'])) {
        $selectedSkill = $_POST['skill'];
        $selectedLevel = $_POST['level'];

        if (!empty($selectedSkill) && !empty($selectedLevel)) {
            $stmtInsert = $mysqlConn->prepare("CALL InsertUserSkill(:email, :skill, :level)");
            $stmtInsert->bindParam(':email', $email);
            $stmtInsert->bindParam(':skill', $selectedSkill);
            $stmtInsert->bindParam(':level', $selectedLevel);
            $stmtInsert->execute();

            $message = "Skill aggiunta con successo!";
            writeLog("Skill aggiunta", ["email" => $email, "skill" => $selectedSkill]);
        } else {
            $message = "Seleziona una skill e un livello.";
        }
    }

    // Rimozione skill
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_skill'])) {
        $skillToRemove = $_POST['remove_skill'];

        $stmtRemove = $mysqlConn->prepare("
            DELETE FROM POSSIEDE
            WHERE Email_Utente = :email AND Competenza_Skill = :skill
        ");
        $stmtRemove->bindParam(':email', $email);
        $stmtRemove->bindParam(':skill', $skillToRemove);
        $stmtRemove->execute();

        $message = "Skill rimossa con successo!";
        writeLog("Skill rimossa", ["email" => $email, "skill" => $skillToRemove]);
    }

    // Recupera skill dell'utente
    $stmtSkill = $mysqlConn->prepare("
        SELECT Competenza_Skill AS Competenza, Livello
        FROM POSSIEDE
        WHERE Email_Utente = :email
    ");
    $stmtSkill->bindParam(':email', $email);
    $stmtSkill->execute();
    $userSkills = $stmtSkill->fetchAll(PDO::FETCH_ASSOC);

    // Recupera skill disponibili
    $stmtAvailable = $mysqlConn->prepare("
        SELECT Competenza
        FROM SKILL
        WHERE Competenza NOT IN (
            SELECT Competenza_Skill FROM POSSIEDE WHERE Email_Utente = :email
        )
    ");
    $stmtAvailable->bindParam(':email', $email);
    $stmtAvailable->execute();
    $availableSkills = $stmtAvailable->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Errore nella query: " . $e->getMessage();
}
?>
<?php include_once 'navbar.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profilo Utente | Bostarter</title>
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
      <h1 class="display-4 text-white mb-4 animate-fadein">Benvenuto nel tuo Profilo</h1>
      <p class="lead text-white-50 animate-fadein" style="animation-delay: 0.2s">Gestisci le tue competenze e skill qui!</p>
    </div>
  </header>

  <div class="container mt-5">
    <div class="card shadow-sm rounded mb-4">
      <div class="card-body">
        <h3 class="card-title text-center">Dati Utente</h3>
        <?php if ($message): ?>
          <div class="alert <?= strpos($message, 'successo') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <?php if ($userData): ?>
          <ul class="list-group">
            <li class="list-group-item"><strong>Nickname:</strong> <?= htmlspecialchars($userData['Nickname']) ?></li>
            <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($userData['Email']) ?></li>
            <li class="list-group-item"><strong>Nome:</strong> <?= htmlspecialchars($userData['Nome']) ?></li>
            <li class="list-group-item"><strong>Cognome:</strong> <?= htmlspecialchars($userData['Cognome']) ?></li>
            <li class="list-group-item"><strong>Anno di nascita:</strong> <?= htmlspecialchars($userData['Anno']) ?></li>
            <li class="list-group-item"><strong>Luogo di nascita:</strong> <?= htmlspecialchars($userData['Luogo']) ?></li>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="card shadow-sm rounded mb-4">
      <div class="card-body">
        <h4>Aggiungi una nuova skill</h4>
        <form method="POST">
          <div class="mb-3">
            <label for="skills" class="form-label">Seleziona una skill:</label>
            <select name="skill" id="skills" class="form-select">
              <?php foreach ($availableSkills as $skill): ?>
                <option value="<?= htmlspecialchars($skill['Competenza']) ?>">
                  <?= htmlspecialchars($skill['Competenza']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="level" class="form-label">Seleziona il livello:</label>
            <select name="level" id="level" class="form-select">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Salva Skill</button>
        </form>
      </div>
    </div>

    <div class="card shadow-sm rounded mb-4">
      <div class="card-body">
        <h4>Le tue skill selezionate</h4>
        <?php if (count($userSkills) > 0): ?>
          <ul class="list-group">
            <?php foreach ($userSkills as $skill): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($skill['Competenza']) ?> (Livello: <?= htmlspecialchars($skill['Livello']) ?>)
                <form method="POST" style="display:inline;">
                  <button type="submit" name="remove_skill" value="<?= htmlspecialchars($skill['Competenza']) ?>" class="btn btn-danger btn-sm">Rimuovi</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted">Nessuna competenza associata.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php include_once 'footer.php'; ?>
</body>
</html>
