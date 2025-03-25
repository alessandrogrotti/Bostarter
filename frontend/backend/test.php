<?php

// Include la configurazione
include('config.php');

// Connessione a MySQL utilizzando la funzione dal file config.php
$conn = connectMySQL(); // Usa la funzione connectMySQL() per la connessione

// Connessione a MongoDB utilizzando la funzione dal file config.php
$mongoClient = connectMongoDB(); // Usa la funzione connectMongoDB() per la connessione
$mongoDb = $mongoClient->Bostarter;  // Nome del database MongoDB
$logsCollection = $mongoDb->logs;  // Collezione logs

// Recupero dei dati (con supporto al filtro per nome)
if (isset($_GET['name'])) {
    $name = $_GET['name'];
    // Usa query preparata per evitare SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ?");
    $likeName = "%" . $name . "%";
    $stmt->bind_param("s", $likeName);
} else {
    $stmt = $conn->prepare("SELECT * FROM users");
}

$stmt->execute();
$result = $stmt->get_result();
$users = [];

// Aggiungi il log di ogni operazione a MongoDB
$logEntry = [
    'action' => 'retrieve_users',
    'timestamp' => new MongoDB\BSON\UTCDateTime(),
    'details' => isset($_GET['name']) ? "Filtered by name: $name" : "No filter applied"
];
$logsCollection->insertOne($logEntry);  // Aggiungi il log a MongoDB

// Recupera i dati da MySQL
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Restituisci i dati come JSON
header('Content-Type: application/json');
echo json_encode($users);

// Chiudi la connessione a MySQL
$stmt->close();
$conn->close();

?>
