<?php

require_once '/var/www/html/backend/vendor/autoload.php';

header("Access-Control-Allow-Origin: *"); // Permette accesso da qualsiasi origine
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Aggiungi i metodi HTTP permessi
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Aggiungi gli headers permessi

// Configurazione MySQL
define('MYSQL_SERVER', 'mysql');  // Nome del servizio MySQL nel file docker-compose.yml
define('MYSQL_USERNAME', 'username');  // Username definito nel file docker-compose.yml
define('MYSQL_PASSWORD', 'password');  // Password definita nel file docker-compose.yml
define('MYSQL_NAME', 'bostarter_db');  // Nome del database definito nel file docker-compose.yml

// Configurazione MongoDB
define('MONGO_SERVER', 'mongodb');  // Nome del servizio MongoDB nel file docker-compose.yml
define('MONGO_USERNAME', 'admin_username');  // Username definito nel file docker-compose.yml
define('MONGO_PASSWORD', 'admin_password');  // Password definita nel file docker-compose.yml

// Connessione MySQL
function connectMySQL() {
    $conn = new mysqli(MYSQL_SERVER, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_NAME);
    
    // Controllo se c'è stato un errore nella connessione
    if ($conn->connect_error) {
        die(json_encode(['status' => 'error', 'message' => 'Connessione fallita a MySQL: ' . $conn->connect_error]));
    }
    return $conn;
}

// Connessione MongoDB
function connectMongoDB() {
    try {
        // Usa il client MongoDB PHP con la configurazione di autenticazione
        $mongoClient = new MongoDB\Client("mongodb://" . MONGO_USERNAME . ":" . MONGO_PASSWORD . "@" . MONGO_SERVER . ":27017");
        return $mongoClient;
    } catch (Exception $e) {
        die(json_encode(['status' => 'error', 'message' => 'Connessione fallita a MongoDB: ' . $e->getMessage()]));
    }
}
?>
