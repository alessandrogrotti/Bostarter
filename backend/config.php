<?php

header("Access-Control-Allow-Origin: *"); // Permette accesso da qualsiasi origine
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Aggiungi i metodi HTTP permessi
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Aggiungi gli headers permessi

// Configurazione MySQL
define('MYSQL_SERVER', 'localhost');  // Nome del servizio MySQL definito nel docker-compose.yml
define('MYSQL_USERNAME', 'username'); // Come configurato nel docker-compose.yml
define('MYSQL_PASSWORD', 'password'); // Come configurato nel docker-compose.yml
define('MYSQL_NAME', 'bostarter_db');  // Come configurato nel docker-compose.yml

// Configurazione MongoDB
define('MONGO_SERVER', 'localhost'); // Nome del servizio MongoDB definito nel docker-compose.yml
define('MONGO_USERNAME', 'admin_username');  // Come configurato nel docker-compose.yml
define('MONGO_PASSWORD', 'admin_password');  // Come configurato nel docker-compose.yml

// Connessione MySQL con PDO
function connectMySQL() {
    try {
        $dsn = "mysql:host=" . MYSQL_SERVER . ";dbname=" . MYSQL_NAME;

        $pdo = new PDO('mysql:host=127.0.0.1;dbname=bostarter_db', 'username', 'password');
        
        return $pdo;
    } catch (PDOException $e) {
        // Log dell'errore e risposta in caso di fallimento
        echo "<script>console.log('Errore di connessione MySQL: " . $e->getMessage() . "');</script>";
        die(json_encode(['status' => 'error', 'message' => 'Connessione fallita a MySQL: ' . $e->getMessage()]));
    }
}

// Connessione MongoDB
function connectMongoDB() {
    try {

        require 'vendor/autoload.php';

        // Usa il client MongoDB PHP con la configurazione di autenticazione
        $mongoClient = new MongoDB\Client("mongodb://admin_username:admin_password@localhost:27017");
        
        return $mongoClient;
    } catch (Exception $e) {
        // Log dell'errore e risposta in caso di fallimento
        echo "<script>console.log('Errore di connessione MongoDB: " . $e->getMessage() . "');</script>";
        die(json_encode(['status' => 'error', 'message' => 'Connessione fallita a MongoDB: ' . $e->getMessage()]));
    }
}
?>
