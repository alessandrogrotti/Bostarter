<?php

// Include la configurazione
include('config.php');

// Connessione a MySQL utilizzando la funzione dal file config.php
$conn = connectMySQL();

// Connessione a MongoDB utilizzando la funzione dal file config.php
$mongoClient = connectMongoDB();
$mongoDb = $mongoClient->Bostarter;
$logsCollection = $mongoDb->logs;

// Recupera i dati JSON inviati tramite POST
$inputData = json_decode(file_get_contents('php://input'), true);

// Verifica se 'name' è presente nei dati
if (isset($inputData['name'])) {
    $name = $inputData['name'];

    // Usa query preparata per evitare SQL injection
    $stmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'User added successfully'];
        
        // Log dell'operazione in MongoDB
        $logEntry = [
            'action' => 'add_user',
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'details' => "User added: $name"
        ];
        $logsCollection->insertOne($logEntry);
    } else {
        $response = ['status' => 'error', 'message' => 'Error adding user'];
    }

    $stmt->close();
} else {
    $response = ['status' => 'error', 'message' => 'Name parameter is missing'];
}

// Restituisce la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);

// Chiude la connessione a MySQL
$conn->close();

?>
