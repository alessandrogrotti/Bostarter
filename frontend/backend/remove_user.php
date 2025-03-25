<?php

// Include il file di configurazione
include('config.php');

// Connessione a MySQL utilizzando la funzione dal file config.php
$conn = connectMySQL();

// Connessione a MongoDB utilizzando la funzione dal file config.php
$mongoClient = connectMongoDB();
$mongoDb = $mongoClient->Bostarter;
$logsCollection = $mongoDb->logs;

// Recupera i dati JSON inviati tramite POST
$inputData = json_decode(file_get_contents('php://input'), true);

// Verifica se 'id' è presente nei dati
if (isset($inputData['id'])) {
    $id = $inputData['id'];

    // Usa query preparata per evitare SQL injection
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'User deleted successfully'];
        
        // Log dell'operazione in MongoDB
        $logEntry = [
            'action' => 'delete_user',
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'details' => "User deleted with ID: $id"
        ];
        $logsCollection->insertOne($logEntry);
    } else {
        $response = ['status' => 'error', 'message' => 'Error removing user'];
    }

    $stmt->close();
} else {
    $response = ['status' => 'error', 'message' => 'ID parameter is missing'];
}

// Restituisce la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);

// Chiude la connessione a MySQL
$conn->close();

?>