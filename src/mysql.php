<?php
include_once 'connection.php';

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
