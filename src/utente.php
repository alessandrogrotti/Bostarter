<?php
session_start();
include_once 'auth.php';
requireLogin();

include_once 'connection.php';
include_once 'navbar.php';
include_once 'mongodb.php';

$mysqlConn = getMySQLConnection(); 
$logCollection = getMongoDBConnection();
$message = "";

// Ottieni l'email dell'utente dalla sessione
$email = $_SESSION['id'];

$userData = [];
$userSkills = [];

try {
    // Recupera i dati dell'utente
    $stmt = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        $message = "Utente non trovato.";
    } else {
        $userSkills = [];
        writeLog("Visualizzazione utente con skill", ["email" => $email]);
    }

    // Salvataggio nuova skill e livello
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['skill']) && isset($_POST['level'])) {
        $selectedSkill = $_POST['skill'];
        $selectedLevel = $_POST['level'];

        if ($selectedSkill && $selectedLevel) {
            // Utilizza la stored procedure InsertUserSkill
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
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_skill'])) {
        $skillToRemove = $_POST['remove_skill'];
        try {
            // Rimuovi la skill dell'utente dal database
            $stmtRemove = $mysqlConn->prepare("DELETE FROM POSSIEDE WHERE Email_Utente = :email AND Competenza_Skill = :skill");
            $stmtRemove->bindParam(':email', $email);
            $stmtRemove->bindParam(':skill', $skillToRemove);
            $stmtRemove->execute();
            
            $message = "Skill rimossa con successo!";
            writeLog("Skill rimossa", ["email" => $email, "skill" => $skillToRemove]);
        } catch (PDOException $e) {
            $message = "Errore nella rimozione della skill: " . $e->getMessage();
        }
    }

    // Recupera la lista aggiornata delle skill dell'utente
    $skillQuery = "
        SELECT P.Competenza_Skill AS Competenza, P.Livello
        FROM POSSIEDE P
        WHERE P.Email_Utente = :email
    ";
    $stmtSkill = $mysqlConn->prepare($skillQuery);
    $stmtSkill->bindParam(':email', $email);
    $stmtSkill->execute();
    $userSkills = $stmtSkill->fetchAll(PDO::FETCH_ASSOC);

    // Recupera tutte le skill disponibili escluse quelle già associate all'utente
    $availableSkillsQuery = "
        SELECT Competenza 
        FROM SKILL 
        WHERE Competenza NOT IN (
            SELECT Competenza_Skill 
            FROM POSSIEDE 
            WHERE Email_Utente = :email
        )
    ";
    $stmtAvailableSkills = $mysqlConn->prepare($availableSkillsQuery);
    $stmtAvailableSkills->bindParam(':email', $email);
    $stmtAvailableSkills->execute();
    $availableSkills = $stmtAvailableSkills->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Errore nella query: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profilo Utente</title>
  <!-- Includi i CDN di Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJvU6vY+S3phI6R5rTKlqK0b6Qz7LQPlGy+hptZ2A2gsn2+oXq3zOgC8Y6tA" crossorigin="anonymous">
</head>
<body>

  <div class="container mt-4">
    <div class="bg-white p-4 shadow-sm rounded">
      <a href="amministratore.php" class="btn btn-primary me-2">Area amministratore</a>
      <a href="indexCreatore.php" class="btn btn-primary me-2">Area creatore</a>
      <h2>Dati Utente</h2>
      
      <?php if ($message): ?>
        <div class="alert <?php echo (strpos($message, 'aggiunta') !== false) ? 'alert-success' : 'alert-danger'; ?>" role="alert">
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

        <h3>Aggiungi una nuova skill:</h3>
        <form method="POST" action="" class="mb-4">
          <div class="mb-3">
            <label for="skills" class="form-label">Seleziona una skill:</label>
            <select name="skill" id="skills" class="form-select">
              <?php 
              foreach ($availableSkills as $skill): ?>
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

          <button type="submit" class="btn btn-primary">Salva</button>
        </form>

        <h3>Lista delle skill selezionate:</h3>
        <?php if (count($userSkills) > 0): ?>
          <ul class="list-group">
            <?php foreach ($userSkills as $skill): ?>
              <li class="list-group-item d-flex justify-content-between">
                <?= htmlspecialchars($skill['Competenza']) ?> (Livello: <?= htmlspecialchars($skill['Livello']) ?>)
                <form method="POST" action="" style="display: inline;">
                  <button type="submit" name="remove_skill" value="<?= htmlspecialchars($skill['Competenza']) ?>" class="btn btn-danger btn-sm">Rimuovi</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Nessuna competenza associata.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <footer class="text-center mt-5 text-muted">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

  <!-- Includi i JS di Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybGz7t5KtvFjcJ8pB95K8GFA0nXkSpFJ6OYl0k4g4C9g55zD0" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0a7e7oQq8r+K75G5f3aR5D1FflaOBVJXyiy5VeXQ39jjmL9P" crossorigin="anonymous"></script>

</body>
</html>
