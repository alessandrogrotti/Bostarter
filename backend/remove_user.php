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

// Verifica se 'id' è presente nei dati
if (isset($inputData['id'])) {
    $id = $inputData['id'];

    // Query preparata per evitare SQL injection
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    
    try {
        $stmt->execute(['id' => $id]);
        $response = ['status' => 'success', 'message' => 'User deleted successfully'];

        // Log dell'operazione in MongoDB
        $logEntry = [
            'action' => 'delete_user',
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'details' => "User deleted with ID: $id"
        ];
        $logsCollection->insertOne($logEntry);
    } catch (PDOException $e) {
        $response = ['status' => 'error', 'message' => 'Error removing user: ' . $e->getMessage()];
    }
} else {
    $response = ['status' => 'error', 'message' => 'ID parameter is missing'];
}

// Restituisce la risposta come JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
