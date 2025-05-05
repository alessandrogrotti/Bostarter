<?php

// Includi il file di connessione e navbar
include_once 'connection.php';
include_once 'navbar.php';
include_once 'mongodb.php';

// Connessioni ai database
$mysqlConn = getMySQLConnection();

// Se la richiesta è in POST, gestiamo l'inserimento del finanziamento
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupera i dati dal form
    $importo = isset($_POST['importo']) ? $_POST['importo'] : null;
    $nome_progetto = isset($_POST['nome_progetto']) ? $_POST['nome_progetto'] : null;
    $codice_reward = isset($_POST['reward']) ? $_POST['reward'] : null;

    // Recupera l'email dell'utente dalla sessione; se non impostata, viene usato un valore di default (da cambiare in produzione)
    $email_utente = isset($_SESSION['email']) ? $_SESSION['email'] : 'utente@example.com';

    // Controllo basilare sui dati ricevuti
    if (!$importo || !$nome_progetto || !$codice_reward) {
        die("Errore: dati mancanti. Assicurati di compilare correttamente il form.");
    }

    // Scrivi un log per il finanziamento
    writeLog('Finanziamento', "Finanziamento effettuato dall'utente $email_utente per il progetto \"$nome_progetto\" con la reward \"$codice_reward\" e importo $importo");

    // Prepara la chiamata alla stored procedure FinanceProject
    $sql = "CALL FinanceProject(:p_Email_Utente, :p_Importo, :p_Nome_Progetto, :p_Codice_Reward)";
    $stmt = $mysqlConn->prepare($sql);
    $stmt->bindParam(':p_Email_Utente', $email_utente);
    $stmt->bindParam(':p_Importo', $importo);
    $stmt->bindParam(':p_Nome_Progetto', $nome_progetto);
    $stmt->bindParam(':p_Codice_Reward', $codice_reward);

    try {
        // Esecuzione della stored procedure
        $stmt->execute();
        $finanziamentoMessage = "Finanziamento completato con successo per il progetto \"" . htmlspecialchars($nome_progetto) . "\"!";
    } catch(PDOException $e) {
        die("Errore durante il finanziamento: " . $e->getMessage());
    }
} else {
    // Se la richiesta è GET, recupera il nome del progetto passato via GET
    $nome_progetto = isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : 'Progetto Sconosciuto';

    // Scrivi un log per la visita alla pagina di finanziamento
    writeLog('Visita finanziamento', "Accesso alla pagina di finanziamento per il progetto \"$nome_progetto\"");

    // Esegui una query per ottenere le reward relative al progetto corrente
    try {
        $stmt = $mysqlConn->prepare("SELECT Codice, Descrizione FROM REWARD WHERE Nome_Progetto = :nomeProgetto");
        $stmt->bindParam(':nomeProgetto', $nome_progetto);
        $stmt->execute();
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Errore durante l'esecuzione della query: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Finanziamento | Bostarter</title>
  <!-- Inclusione Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container mt-5">
    <?php if ($_SERVER["REQUEST_METHOD"] === "POST") : ?>
      <!-- Se il finanziamento è stato elaborato, mostriamo un messaggio di conferma -->
      <div class="alert alert-success" role="alert">
        <?php echo $finanziamentoMessage; ?>
      </div>
    <?php else : ?>
      <!-- Visualizzazione del form di finanziamento -->
      <div class="card">
        <div class="card-header">
          <h3>Finanzia il progetto "<?php echo $nome_progetto; ?>"</h3>
        </div>
        <div class="card-body">
          <form action="finanziamento.php" method="POST">
            <!-- Campo importo -->
            <div class="mb-3">
              <label for="importo" class="form-label">Importo:</label>
              <input type="text" class="form-control" id="importo" name="importo" required>
            </div>
            <!-- Selezione reward -->
            <div class="mb-3">
              <label class="form-label">Scegli una reward:</label>
              <ul class="list-group">
                <?php
                if(count($rewards) > 0) {
                    foreach($rewards as $reward) {
                        // Uso del codice della reward come valore per il radio button.
                        $codiceReward = htmlspecialchars($reward['Codice']);
                        $descrReward = htmlspecialchars($reward['Descrizione']);
                        echo '<li class="list-group-item">';
                        echo '  <div class="form-check">';
                        echo '    <input class="form-check-input" type="radio" name="reward" id="reward_' . $codiceReward . '" value="' . $codiceReward . '" required>';
                        echo '    <label class="form-check-label" for="reward_' . $codiceReward . '">';
                        echo '      ' . $descrReward;
                        echo '    </label>';
                        echo '  </div>';
                        echo '</li>';
                    }
                } else {
                    echo '<li class="list-group-item">Non sono presenti reward per questo progetto.</li>';
                }
                ?>
              </ul>
            </div>
            <!-- Campo hidden per mantenere il nome del progetto -->
            <input type="hidden" name="nome_progetto" value="<?php echo $nome_progetto; ?>">
            <button type="submit" class="btn btn-primary">Invia</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </main>

  <footer class="text-center mt-5 py-3 bg-light">
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>
  <!-- Inclusione Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
