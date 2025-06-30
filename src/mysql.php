<?php
include_once 'connection.php';

function inserisciCompetenza($nomeCompetenza) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciCompetenza(?)");
        $stmt->bindParam(1, $nomeCompetenza, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (Exception $e) {
        throw new Exception("Errore durante l'inserimento della competenza.");
    }
}

function eliminaCompetenza($competenza) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL EliminaCompetenza(?)");
        $stmt->bindParam(1, $competenza, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (Exception $e) {
        throw new Exception("Errore durante l'eliminazione della competenza.");
    }
}

function ottieniCompetenze() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniCompetenze()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Errore durante l'ottenimento delle competenze.");
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
        throw new Exception("Errore durante l'inserimento del componente.");
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
        throw new Exception("Errore durante l'ottenimento dei componenti per il progetto.");
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
        throw new Exception("Errore durante l'eliminazione del componente.");
    }
}

function eliminaProfilo($id) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL EliminaProfilo(?)");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (Exception $e) {
        throw new Exception("Errore durante l'eliminazione del profilo.");
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
        throw new Exception("Errore durante l'ottenimento dei profili per il progetto.");
    }
}


function OttieniListaAffidabilità() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniListaAffidabilità()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Errore durante l'ottenimento della classifica affidabilità.");
    }
}


function OttieniListaProgetti() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniListaProgetti()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Errore durante l'ottenimento della classifica progetti.");
    }
}

function OttieniListaFinanziatori() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL OttieniListaFinanziatori()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Errore durante l'ottenimento della lista finanziatori.");
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
        throw new Exception("Errore durante l'ottenimento delle candidature per il profilo.");
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
        throw new Exception("Errore durante la gestione della candidatura.");
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
        throw new Exception("Errore durante l'ottenimento dei reward per il progetto.");
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
        throw new Exception("Errore durante l'esecuzione del finanziamento.");
    }
}

function ottieniProgettiDisponibili() {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->query("CALL OttieniProgettiDisponibili()");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        throw new Exception("Errore durante l'ottenimento dei progetti disponibili.");
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
        throw new Exception("Errore durante l'ottenimento delle foto del progetto.");
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
        throw new Exception("Errore durante l'ottenimento dei progetti del creatore.");
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
        throw new Exception("Errore durante la verifica del progetto del creatore.");
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
        throw new Exception("Errore durante l'inserimento del reward.");
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
    } catch (Exception $e) {
        throw new Exception("Errore durante l'inserimento del progetto.");
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
        throw new Exception("Errore durante l'inserimento della foto del progetto.");
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
        throw new Exception("Errore durante l'ottenimento dei dettagli del progetto.");
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
        throw new Exception("Errore durante l'invio della risposta al commento.");
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
        throw new Exception("Errore durante l'inserimento del commento.");
    }
}

function inviaCandidatura($emailUtente, $idProfilo) {
    try {
        $conn = getMySQLConnection();
        $stmt = $conn->prepare("CALL InserisciCandidatura(?, ?)");
        $stmt->bindParam(1, $emailUtente, PDO::PARAM_STR);
        $stmt->bindParam(2, $idProfilo, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        if ($e->getCode() === '45001') {
            throw new Exception("Un'altra candidatura è già stata accettata per questo profilo.");
        } elseif ($e->getCode() === '45002') {
            throw new Exception("Hai già inviato una candidatura per questo profilo.");
        } elseif ($e->getCode() === '45003') {
            throw new Exception("Non possiedi le competenze richieste o il livello minimo per candidarti a questo profilo.");
        } elseif ($e->getCode() === '45004') {
            throw new Exception("Il progetto è chiuso.");
        } else {
            throw new Exception("Errore durante l'invio della candidatura.");
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
        throw new Exception("Errore durante l'ottenimento dei commenti del progetto.");
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
        throw new Exception("Errore durante l'ottenimento dei dati dell'utente.");
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
        throw new Exception("Errore durante la verifica del creatore.");
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
        throw new Exception("Errore durante la verifica dell'admin.");
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
        throw new Exception("Errore durante l'ottenimento del codice admin.");
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

        return $codiceSicurezzaChiaro;
    } catch (Exception $e) {
        throw new Exception("Errore durante la registrazione dell'utente.");
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
        throw new Exception("Errore durante l'ottenimento dei dati dell'utente.");
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
        throw new Exception("Errore durante l'ottenimento delle skill dell'utente.");
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
        throw new Exception("Errore durante l'ottenimento delle competenze disponibili.");
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
        throw new Exception("Errore durante l'ottenimento delle candidature dell'utente.");
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
        throw new Exception("Errore durante l'aggiunta della skill all'utente.");
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
        throw new Exception("Errore durante la rimozione della skill dall'utente.");
    }
}

function inserisciProfiloConCompetenze($nomeProfilo, $nomeProgetto, $skills, $levels) {
    try {
        $conn = getMySQLConnection();

        $stmtProfilo = $conn->prepare("CALL InserisciProfilo(?, ?)");
        $stmtProfilo->bindParam(1, $nomeProfilo, PDO::PARAM_STR);
        $stmtProfilo->bindParam(2, $nomeProgetto, PDO::PARAM_STR);
        $stmtProfilo->execute();

        $stmtId = $conn->prepare("CALL OttieniUltimoIdProfilo(@UltimoId)");
        $stmtId->execute();
        $stmtIdResult = $conn->query("SELECT @UltimoId AS Id");
        $profiloId = $stmtIdResult->fetch(PDO::FETCH_ASSOC)['Id'];

        $stmtSkill = $conn->prepare("CALL InserisciRichiede(?, ?, ?)");
        foreach ($skills as $index => $competenza) {
            if (
                !isset($levels[$index]) ||
                !is_numeric($levels[$index]) ||
                (int)$levels[$index] < 1 ||
                (int)$levels[$index] > 5
            ) {
                continue;
            }

            $livello = (int)$levels[$index];

            $stmtSkill->bindParam(1, $livello, PDO::PARAM_INT);
            $stmtSkill->bindParam(2, $profiloId, PDO::PARAM_INT);
            $stmtSkill->bindParam(3, $competenza, PDO::PARAM_STR);
            $stmtSkill->execute();
        }

        return true;
    } catch (Exception $e) {
        throw new Exception("Errore durante l'inserimento del profilo con competenze.");
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
        throw new Exception("Errore durante l'ottenimento dei progetti con foto.");
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
        throw new Exception("Errore durante l'ottenimento dei dettagli del progetto.");
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
        throw new Exception("Errore durante l'ottenimento dei commenti del progetto.");
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
        throw new Exception("Errore durante l'ottenimento della risposta al commento.");
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
        throw new Exception("Errore durante l'ottenimento delle foto del progetto.");
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
        throw new Exception("Errore durante l'ottenimento dei reward del progetto.");
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
        throw new Exception("Errore durante l'invio della risposta al commento.");
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
        throw new Exception("Errore durante l'ottenimento dell'email del creatore del progetto.");
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
        throw new Exception("Errore durante l'inserimento del commento.");
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
        throw new Exception("Errore durante la verifica del finanziamento odierno.");
    }
}

?>
