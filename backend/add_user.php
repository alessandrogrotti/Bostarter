<?php

// Include la configurazione
include('config.php');

// Connessione a MySQL con PDO
$conn = connectMySQL();

// Connessione a MongoDB
$mongoClient = connectMongoDB();
$mongoDb = $mongoClient->Bostarter;
$logsCollection = $mongoDb->logs;

// Recupera i dati JSON inviati tramite POST
$inputData = json_decode(file_get_contents('php://input'), true);

// Verifica se 'name' è presente nei dati
if (isset($inputData['name'])) {
    $name = $inputData['name'];

    // Query preparata per evitare SQL injection
    $stmt = $conn->prepare("INSERT INTO users (name) VALUES (:name)");
    
    try {
        $stmt->execute(['name' => $name]);
        $response = ['status' => 'success', 'message' => 'User added successfully'];

        // Log dell'operazione in MongoDB
        $logEntry = [
            'action' => 'add_user',
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'details' => "User added: $name"
        ];
        $logsCollection->insertOne($logEntry);
    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Error adding user: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'Name parameter is missing'];
}

// Restituisce la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>