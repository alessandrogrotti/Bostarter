<?php
include 'connection.php';
include 'navbar.php';

$mysqlConn = getMySQLConnection(); // deve essere PDO
$logCollection = getMongoDBConnection();
$message = "";

// Email utente da caricare (può essere anche dinamica se fai login)
$email = 'creatore@esempio.com';

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
        // La lista delle skill selezionate è vuota all'inizio
        $userSkills = [];

        // Aggiungi il log della visualizzazione
        writeLog("Visualizzazione utente con skill", ["email" => $email]);
    }

    // Salvataggio nuova skill e livello
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        } else {
            $message = "Seleziona una skill e un livello.";
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

} catch (PDOException $e) {
    $message = "Errore nella query: " . $e->getMessage();
}

function writeLog($action, $details) {
    global $logCollection;

    $logEntry = [
        'action' => $action,
        'details' => $details,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),
    ];

    $bulkWrite = new MongoDB\Driver\BulkWrite;
    $bulkWrite->insert($logEntry);

    try {
        $logCollection->executeBulkWrite('Bostarter.logs', $bulkWrite);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Errore durante la scrittura del log: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Profilo Utente</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
  <h2>Dati Utente</h2>
  <?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if ($userData): ?>
    <p><strong>Nickname:</strong> <?= htmlspecialchars($userData['Nickname']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($userData['Email']) ?></p>
    <p><strong>Nome:</strong> <?= htmlspecialchars($userData['Nome']) ?></p>
    <p><strong>Cognome:</strong> <?= htmlspecialchars($userData['Cognome']) ?></p>
    <p><strong>Anno di nascita:</strong> <?= htmlspecialchars($userData['Anno']) ?></p>
    <p><strong>Luogo di nascita:</strong> <?= htmlspecialchars($userData['Luogo']) ?></p>

    <h3>Aggiungi una nuova skill:</h3>
    <form method="POST" action="">
      <label for="skills">Seleziona una skill:</label>
      <select name="skill" id="skills">
        <?php 
        // Recuperiamo tutte le skill possibili disponibili da SKILL (non da POSSIEDE)
        $availableSkillsQuery = "SELECT Competenza FROM SKILL";
        $stmtAvailableSkills = $mysqlConn->prepare($availableSkillsQuery);
        $stmtAvailableSkills->execute();
        $availableSkills = $stmtAvailableSkills->fetchAll(PDO::FETCH_ASSOC);

        foreach ($availableSkills as $skill): ?>
          <option value="<?= htmlspecialchars($skill['Competenza']) ?>">
            <?= htmlspecialchars($skill['Competenza']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="level">Seleziona il livello:</label>
      <select name="level" id="level">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>

      <button type="submit">Salva</button>
    </form>

    <h3>Lista delle skill selezionate:</h3>
    <?php if (count($userSkills) > 0): ?>
      <ul>
        <?php foreach ($userSkills as $skill): ?>
          <li><?= htmlspecialchars($skill['Competenza']) ?> (Livello: <?= htmlspecialchars($skill['Livello']) ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Nessuna competenza associata.</p>
    <?php endif; ?>
  <?php endif; ?>
</main>

<footer>
  <p>Progetto Bostarter &copy; 2025</p>
</footer>

</body>
</html>

