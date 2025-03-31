<?php
// Connessione a MySQL usando PDO
function getMySQLConnection() {
    // Impostazioni di connessione MySQL
    $servername = "mysql";
    $username = "username";  // Sostituisci con il tuo username MySQL
    $password = "password";  // Sostituisci con la tua password MySQL
    $dbname = "bostarter_db";  // Nome del tuo database MySQL

    try {
        // Crea una connessione PDO a MySQL
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // Imposta l'errore PDO in modalità eccezione
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connessione fallita a MySQL: " . $e->getMessage());
    }
}

// Connessione a MongoDB usando MongoDB\Driver\Manager
function getMongoDBConnection() {
    try {
        // Crea una connessione a MongoDB
        $manager = new MongoDB\Driver\Manager("mongodb://admin_username:admin_password@mongo:27017");
        return $manager;
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Connessione fallita a MongoDB: " . $e->getMessage());
    }
}

// Test di connessione (opzionale)
$mysqlConn = getMySQLConnection();
$mongoConn = getMongoDBConnection();

// Puoi anche eseguire altre azioni di test, ad esempio:
// echo "Connessione a MySQL e MongoDB avvenuta con successo!";
?>
