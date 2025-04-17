<?php
include_once 'auth.php';

session_start();
requireCreator();
?>

<?php
include_once 'navbar.php';

?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css"> 
  <title>Homepage - Creator</title>
</head>
<body>

  <main>
    <section>
      <button onclick="window.location.href='nuovoProgetto.html';"> Inserisci nuovo progetto </button>
      <h2> Progetti: </h2>
    </section>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
