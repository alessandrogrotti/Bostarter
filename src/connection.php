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

// Connessione a MongoDB senza librerie esterne (usando MongoDB\Driver\Manager)
function getMongoDBConnection() {
    try {
        // Creazione della connessione al server MongoDB
        $manager = new MongoDB\Driver\Manager("mongodb://admin_username:admin_password@mongodb:27017");
        return $manager;
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Connessione fallita a MongoDB: " . $e->getMessage());
    }
}
?>
