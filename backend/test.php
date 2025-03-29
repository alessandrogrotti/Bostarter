<?php

// Include la configurazione
include('config.php');

// Connessione a MySQL con PDO
$conn = connectMySQL(); 

// Connessione a MongoDB
$mongoClient = connectMongoDB();
$mongoDb = $mongoClient->Bostarter;
$logsCollection = $mongoDb->logs;

// Recupero dei dati (con supporto al filtro per nome)
if (isset($_GET['name'])) {
    $name = "%" . $_GET['name'] . "%";
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ?");
    $stmt->execute([$name]); // Passaggio di parametri per PDO
} else {
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC); // Con PDO si usa fetchAll()

// Log dell'operazione in MongoDB
$logEntry = [
    'action' => 'retrieve_users',
    'timestamp' => new MongoDB\BSON\UTCDateTime(),
    'details' => isset($_GET['name']) ? "Filtered by name: {$_GET['name']}" : "No filter applied"
];

$logsCollection->insertOne($logEntry);  // Aggiungi il log a MongoDB

// Restituisci i dati come JSON
header('Content-Type: application/json');
echo json_encode($users);
?>
