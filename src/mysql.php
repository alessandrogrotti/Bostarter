<?php
include_once 'connection.php';

// Funzioni per competenze
function inserisciCompetenza($nomeCompetenza) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciCompetenza(?)");
        $stmt->bindParam(1, $nomeCompetenza, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function eliminaCompetenza($competenza) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL EliminaCompetenza(?)");
        $stmt->bindParam(1, $competenza, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function ottieniCompetenze() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCompetenze()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Funzioni per componenti
function inserisciComponente($nome, $nomeProgettoHardware, $descrizione, $prezzo, $quantita) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciComponente(?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $nome, PDO::PARAM_STR);
        $stmt->bindParam(2, $nomeProgettoHardware, PDO::PARAM_STR);
        $stmt->bindParam(3, $descrizione, PDO::PARAM_STR);
        $stmt->bindParam(4, $prezzo, PDO::PARAM_STR);
        $stmt->bindParam(5, $quantita, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function ottieniComponentiPerProgetto($nomeProgettoHardware) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniComponentiPerProgetto(?)");
        $stmt->bindParam(1, $nomeProgettoHardware, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function eliminaComponente($nome, $nomeProgettoHardware) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL EliminaComponente(?, ?)");
        $stmt->bindParam(1, $nome, PDO::PARAM_STR);
        $stmt->bindParam(2, $nomeProgettoHardware, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

/*
function eliminaProfilo($nome, $nomeProgettoHardware) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL EliminaComponente(?, ?)");
        $stmt->bindParam(1, $nome, PDO::PARAM_STR);
        $stmt->bindParam(2, $nomeProgettoHardware, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}
*/

function ottieniProfiliPerProgetto($nomeProgettoSoftware) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniProfiliPerProgetto(?)");
        $stmt->bindParam(1, $nomeProgettoSoftware, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}


function OttieniListaAffidabilità() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniListaAffidabilità()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}


function OttieniListaProgetti() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniListaProgetti()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function OttieniListaFinanziatori() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniListaFinanziamenti()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>
