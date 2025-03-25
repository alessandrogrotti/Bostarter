<?php

// Include il file di configurazione
include('config.php');

// Connessione a MySQL utilizzando la funzione dal file config.php
$conn = connectMySQL(); // Usa la funzione connectMySQL() per la connessione

// Recupera i dati JSON inviati tramite POST
$inputData = json_decode(file_get_contents('php://input'), true);  // Legge il corpo della richiesta

// Verifica se 'id' è presente nei dati
if (isset($inputData['id'])) {
    $id = $inputData['id'];

    // Usa query preparata per evitare SQL injection
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error removing user']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID parameter is missing']);
}

// Chiudi la connessione a MySQL
$conn->close();

?>
