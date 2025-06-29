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

function eliminaProfilo($id) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL EliminaProfilo(?)");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

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
        $stmt = $conn->prepare("CALL OttieniListaFinanziatori()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function ottieniCandidaturePerProfilo($idProfilo) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCandidaturePerProfilo(?)");
        $stmt->bindParam(1, $idProfilo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function gestisciCandidatura($idCandidatura, $stato) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL GestisciCandidatura(?, ?)");
        $stmt->bindParam(1, $idCandidatura, PDO::PARAM_INT);
        $stmt->bindParam(2, $stato, PDO::PARAM_STR); 
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function ottieniRewardPerProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniRewardProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function eseguiFinanziamento($emailUtente, $importo, $nomeProgetto, $codiceReward) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL FinanziaProgetto(?, ?, ?, ?)");
        $stmt->bindParam(1, $emailUtente, PDO::PARAM_STR);
        $stmt->bindParam(2, $importo, PDO::PARAM_STR);
        $stmt->bindParam(3, $nomeProgetto, PDO::PARAM_STR);
        $stmt->bindParam(4, $codiceReward, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniProgettiDisponibili() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->query("CALL OttieniProgettiDisponibili()");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniFotoProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniFotoProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        return [];
    }
}

function ottieniProgettiCreatore($emailCreatore) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniProgettiCreatore(?)");
        $stmt->bindParam(1, $emailCreatore, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function verificaProgettoCreatore($nomeProgetto, $emailCreatore) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL VerificaProgettoCreatore(?, ?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->bindParam(2, $emailCreatore, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

function inserisciReward($descrizione, $foto, $nomeProgetto, $emailCreatore) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciReward(?, ?, ?, ?)");
        $stmt->bindParam(1, $descrizione, PDO::PARAM_STR);
        $stmt->bindParam(2, $foto, PDO::PARAM_STR);
        $stmt->bindParam(3, $nomeProgetto, PDO::PARAM_STR);
        $stmt->bindParam(4, $emailCreatore, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function inserisciProgetto($nome, $emailCreatore, $descrizione, $dataLimite, $budget, $tipo) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciProgetto(?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $nome, PDO::PARAM_STR);
        $stmt->bindParam(2, $emailCreatore, PDO::PARAM_STR);
        $stmt->bindParam(3, $descrizione, PDO::PARAM_STR);
        $stmt->bindParam(4, $dataLimite, PDO::PARAM_STR);
        $stmt->bindParam(5, $budget, PDO::PARAM_STR);
        $stmt->bindParam(6, $tipo, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        throw $e;
    }
}

function inserisciFotoProgetto($fotoPath, $nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciFotoProgetto(?, ?)");
        $stmt->bindParam(1, $fotoPath, PDO::PARAM_STR);
        $stmt->bindParam(2, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniDettagliProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniDettagliProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

function rispondiACommento($testo, $email, $nomeProgetto, $idCommento) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL RispostaCommento(?, ?, ?, ?)");
        $stmt->bindParam(1, $testo, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $nomeProgetto, PDO::PARAM_STR);
        $stmt->bindParam(4, $idCommento, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function inserisciCommento($testo, $email, $nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciCommento(?, ?, ?)");
        $stmt->bindParam(1, $testo, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function inviaCandidatura($emailUtente, $idProfilo) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciCandidatura(?, ?)");
        $stmt->bindParam(1, $emailUtente, PDO::PARAM_STR);
        $stmt->bindParam(2, $idProfilo, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        if ($e->getCode() === '45001') {
            throw new Exception("Un'altra candidatura è già stata accettata per questo profilo.");
        } elseif ($e->getCode() === '45002') {
            throw new Exception("Hai già inviato una candidatura per questo profilo.");
        } elseif ($e->getCode() === '45003') {
            throw new Exception("Non possiedi le competenze richieste o il livello minimo per candidarti a questo profilo.");
        } elseif ($e->getCode() === '45004') {
            throw new Exception("Non puoi candidarti a un profilo che richiede competenze che non possiedi.");
        } else {
            throw $e;
        }
    }
}

function ottieniCommentiProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCommentiProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function ottieniUtente($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniUtente(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function verificaCreatore($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL VerificaCreatore(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        throw $e;
    }
}

function verificaAdmin($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL VerificaAdmin(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniCodiceAdmin($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCodiceAdmin(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['Codice_Sicurezza'] ?? null;
    } catch (Exception $e) {
        throw $e;
    }
}

function registraUtente($email, $nickname, $password, $nome, $cognome, $luogoNascita, $annoNascita, $isCreator, $isAdministrator, &$codiceSicurezzaChiaro = null) {
    try {
        $conn = getMySQLConnection();

        $passwordHash = md5($password);

        $stmt = $conn->prepare("CALL RegistraUtente(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->bindParam(2, $nickname, PDO::PARAM_STR);
        $stmt->bindParam(3, $passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(4, $luogoNascita, PDO::PARAM_STR);
        $stmt->bindParam(5, $annoNascita, PDO::PARAM_STR);
        $stmt->bindParam(6, $nome, PDO::PARAM_STR);
        $stmt->bindParam(7, $cognome, PDO::PARAM_STR);
        $stmt->execute();

        if ($isCreator) {
            $stmtCreator = $conn->prepare("INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) VALUES (:email, 0, 0.00)");
            $stmtCreator->execute([':email' => $email]);
        }

        if ($isAdministrator) {
            $codiceSicurezzaChiaro = "SEC" . rand(1000, 9999);
            $codiceSicurezzaHash = md5($codiceSicurezzaChiaro);

            $stmtAdmin = $conn->prepare("INSERT INTO AMMINISTRATORE (Email_Utente, Codice_Sicurezza) VALUES (:email, :codice)");
            $stmtAdmin->execute([
                ':email' => $email,
                ':codice' => $codiceSicurezzaHash
            ]);
        }

        return $codiceSicurezzaChiaro; // Restituisci il codice di sicurezza (null se non è admin)
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniDatiUtente($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniDatiUtente(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniSkillUtente($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniSkillUtente(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniCompetenzeDisponibili($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCompetenzeDisponibili(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function ottieniCandidatureUtente($email) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCandidatureUtente(?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function aggiungiSkillUtente($email, $competenza, $livello) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL AggiungiSkillUtente(?, ?, ?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->bindParam(2, $competenza, PDO::PARAM_STR);
        $stmt->bindParam(3, $livello, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function rimuoviSkillUtente($email, $competenza) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL RimuoviSkillUtente(?, ?)");
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->bindParam(2, $competenza, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function inserisciProfiloConCompetenze($nomeProfilo, $nomeProgetto, $skills, $levels) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciProfiloRichiede(?, ?, ?, ?)");
        $stmt->execute(1, $nomeProfilo, PDO::PARAM_STR);
        $stmt->execute(2, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute(3, $skills, PDO::PARAM_STR);
        $stmt->execute(4, $levels, PDO::PARAM_STR);
        $stmtSkill->execute();
    } catch (Exception $e) {
        return false;
    }
}

function ottieniProgettiConFoto() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->query("CALL OttieniProgettiDisponibili()");
        $progetti = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        foreach ($progetti as &$progetto) {
            $nomeProgetto = $progetto['Nome'];
            $fotoStmt = $conn->prepare("CALL OttieniFotoProgetto(?)");
            $fotoStmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
            $fotoStmt->execute();
            $progetto['Foto'] = $fotoStmt->fetchAll(PDO::FETCH_COLUMN);
            $fotoStmt->closeCursor();
        }
        return $progetti;
    } catch (Exception $e) {
        throw $e;
    }
}

function getDettagliProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniDettagliProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function getCommentiProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCommentiProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function getRispostaCommento($idCommento) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniRispostaCommento(?)");
        $stmt->bindParam(1, $idCommento, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function getFotoProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniFotoProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function getRewardsProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniRewardProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw $e;
    }
}

function inviaRispostaCommento($testo, $email, $nomeProgetto, $idCommento) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL RispostaCommento(?, ?, ?, ?)");
        $stmt->bindParam(1, $testo, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $nomeProgetto, PDO::PARAM_STR);
        $stmt->bindParam(4, $idCommento, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function getEmailCreatoreProgetto($nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniEmailCreatoreProgetto(?)");
        $stmt->bindParam(1, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn(); 
    } catch (Exception $e) {
        throw $e;
    }
}

function inviaCommento($testo, $email, $nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciCommento(?, ?, ?)");
        $stmt->bindParam(1, $testo, PDO::PARAM_STR);
        $stmt->bindParam(2, $email, PDO::PARAM_STR);
        $stmt->bindParam(3, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        throw $e;
    }
}

function verificaFinanziamentoOggi($emailUtente, $nomeProgetto) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL VerificaFinanziamentoOggi(?, ?)");
        $stmt->bindParam(1, $emailUtente, PDO::PARAM_STR);
        $stmt->bindParam(2, $nomeProgetto, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        throw $e;
    }
}

?>
