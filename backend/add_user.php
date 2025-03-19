<?php
// Include la configurazione
include('config.php');

// Connessione a MySQL utilizzando la funzione dal file config.php
$conn = connectMySQL(); // Usa la funzione connectMySQL() per la connessione

// Recupera i dati JSON inviati tramite POST
$inputData = json_decode(file_get_contents('php://input'), true);  // Legge il corpo della richiesta

// Verifica se 'name' è presente nei dati
if (isset($inputData['name'])) {
    $name = $inputData['name'];
    
    // Usa query preparata per evitare SQL injection
    $stmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding user']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Name parameter is missing']);
}

// Chiudi la connessione a MySQL
$conn->close();
?>
