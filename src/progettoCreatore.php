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
  <link rel="stylesheet" href="../style.css"> 
  <title>Project - creator</title>
</head>
<body>

  <main>
    <!--Generale-->
    <section>
      <h2> Titolo </h2>
      <p> Descrizione </p>
      <img src="" alt="Immagine Progetto">
    </section>

    <!--Finanziamento-->
    <section>
      <button onclick="window.location.href='finanziamento.html';"> Finanzia questo progetto! </button>
    </section>

    <!--Reward-->
    <section>
    <label> Inserisci reward: </label>
      <input type="text" id="reward" required> </input>
      <input type="file" id="fileInput" accept="image/*">
      <img id="preview" src="" alt="Anteprima" style="max-width: 300px; display: none;">
      <button type="submit"> Aggiungi </button>
    </section>
    <section>
      <h4> Lista delle reward: </h4>
      <ul> 
          <li> Reward 1</li>
          <li> Reward 2</li>
      </ul>
    </section>
    
    <!--Commenti-->
    <section>
      <label> Inserisci commento: </label>
      <input type="text" id="commento" required> </input>
      <button type="submit"> Aggiungi </button>
      <p> Lista: </p>
      <ul>
          <li> 
              <p> Commento 1 </p> 
              <button> Rispondi </button>
              <input type="text" id="rispostaCommento" required> </input>
              <button type="submit"> Invia </button>
          </li>
          <li> 
              <p> Commento 2 </p> 
              <button> Rispondi </button>
              <input type="text" id="rispostaCommento" required> </input>
              <button type="submit"> Invia </button>
          </li>
      </ul>
    </section>
  
    <!--Profili-->
    <section>
      <button> Inserisci profilo </button>
      <select name="competenze">
        <option selected value="selected">--- select one ---</option>
      </select>
      <select name="competenzeLivello">
        <option selected value="selected">--- select one ---</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
      </select>
      <button> Manda! </button>
    </section>
    <section>
      <h4> Profili richiesti:</h4>
      <ul>
          <li> 
            <p>Profilo 1 </p> 
            <p> candidature: </p>
            <ol>
              <li> 
                <p> candidatura 1 </p>
                <button> Accetta </button>
                <button> Rifiuta </button>
              </li>
            </ol>
          </li>
          <li> 
            <p>Profilo 2 </p> 
          </li>
      </ul>
    </section>
  </main>

  <footer>
    <p>Progetto Bostarter &copy; 2025</p>
  </footer>

</body>
</html>
