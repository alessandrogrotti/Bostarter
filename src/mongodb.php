<?php
include_once 'connection.php';

$logCollection = getMongoDBConnection(); // Connessione a MongoDB

// Funzione per scrivere nel log
function writeLog($action, $details) {
    global $logCollection;  // Usa la connessione globale a MongoDB

    $logEntry = [
        'action' => $action,
        'details' => $details,
        'timestamp' => new MongoDB\BSON\UTCDateTime(),  // Genera un timestamp
    ];

    $bulkWrite = new MongoDB\Driver\BulkWrite;
    $bulkWrite->insert($logEntry);

    try {
        // Esegui l'inserimento nel database
        $logCollection->executeBulkWrite('Bostarter.logs', $bulkWrite);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Errore durante la scrittura del log: " . $e->getMessage());
    }
}
?>
