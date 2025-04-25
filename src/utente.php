<?php
include_once 'auth.php';
requireLogin();

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
  <title>Profilo Utente</title>
  <link rel="stylesheet" href="style.css">
  <!-- Includi i CDN di Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJvU6vY+S3phI6R5rTKlqK0b6Qz7LQPlGy+hptZ2A2gsn2+oXq3zOgC8Y6tA" crossorigin="anonymous">
</head>
<body>
  <div class="container mt-4">
    <div class="bg-white p-4 shadow-sm rounded">
      <h2>Dati Utente</h2>

      <?php if ($message): ?>
        <div class="alert <?= strpos($message, 'successo') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <?php if ($userData): ?>
        <div class="row">
          <div class="col-md-6">
            <p><strong>Nickname:</strong> <?= htmlspecialchars($userData['Nickname']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($userData['Email']) ?></p>
            <p><strong>Nome:</strong> <?= htmlspecialchars($userData['Nome']) ?></p>
            <p><strong>Cognome:</strong> <?= htmlspecialchars($userData['Cognome']) ?></p>
            <p><strong>Anno di nascita:</strong> <?= htmlspecialchars($userData['Anno']) ?></p>
            <p><strong>Luogo di nascita:</strong> <?= htmlspecialchars($userData['Luogo']) ?></p>
          </div>
        </div>

        <h3 class="mt-4">Aggiungi una nuova skill</h3>
        <form method="POST" class="mb-4">
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

          <button type="submit" class="btn btn-success">Salva</button>
        </form>

        <h3>Lista delle skill selezionate</h3>
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
          <p>Nessuna competenza associata.</p>
        <?php endif; ?>
      <?php endif; ?>
                                                                      

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
