DROP DATABASE IF EXISTS bostarter_db;
CREATE DATABASE IF NOT EXISTS bostarter_db;
USE bostarter_db;

CREATE TABLE UTENTE (
    Email VARCHAR(255) PRIMARY KEY,
    Nickname VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Luogo VARCHAR(100),
    Anno YEAR,
    Nome VARCHAR(100),
    Cognome VARCHAR(100)
) ENGINE = "INNODB";

CREATE TABLE CREATORE (
    Email_Utente VARCHAR(255) PRIMARY KEY,
    Nr_progetti INT DEFAULT 0,
    Affidabilità DECIMAL(3, 2),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE AMMINISTRATORE (
    Email_Utente VARCHAR(255) PRIMARY KEY,
    Codice_Sicurezza VARCHAR(50) NOT NULL,
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE PROGETTO (
    Nome VARCHAR(100) PRIMARY KEY,
    Email_Creatore VARCHAR(255),
    Descrizione TEXT,
    Data_Inserimento DATE,
    Data_Limite DATE,
    Budget DECIMAL(10, 2),
    Stato VARCHAR(50),
    Tipo ENUM ('Hardware', 'Software'),
    FOREIGN KEY (Email_Creatore) REFERENCES CREATORE(Email_Utente) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE FOTO (
    Valore VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    PRIMARY KEY (Valore, Nome_Progetto),
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE COMMENTO (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Data DATE,
    Testo TEXT,
    Email_Utente VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email) ON DELETE CASCADE,
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE RISPOSTA (
    Id_Commento INT,
    Id_Risposta INT,
    PRIMARY KEY (Id_Commento, Id_Risposta),
    FOREIGN KEY (Id_Commento) REFERENCES COMMENTO(Id) ON DELETE CASCADE,
    FOREIGN KEY (Id_Risposta) REFERENCES COMMENTO(Id) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE REWARD (
    Codice INT PRIMARY KEY AUTO_INCREMENT,
    Descrizione TEXT,
    Foto VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE FINANZIAMENTO (
    Data DATE,
    Importo DECIMAL(10, 2),
    Email_Utente VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    Codice_Reward INT,
    PRIMARY KEY (Data, Email_Utente, Nome_Progetto),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email) ON DELETE CASCADE,
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome) ON DELETE CASCADE,
    FOREIGN KEY (Codice_Reward) REFERENCES REWARD(Codice) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE COMPONENTE (
    Nome VARCHAR(100),
    Nome_ProgettoHardware VARCHAR(100),
    Descrizione VARCHAR(100),
    Prezzo DOUBLE,
    Quantità INT,
    PRIMARY KEY (Nome_ProgettoHardware, Nome),
    FOREIGN KEY (Nome_ProgettoHardware) REFERENCES PROGETTO(Nome) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE PROFILO (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100),
    Nome_ProgettoSoftware VARCHAR(100),
    FOREIGN KEY (Nome_ProgettoSoftware) REFERENCES PROGETTO(Nome) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE RICHIEDE (
    Livello INT,
    Id_Profilo INT,
    Competenza_Skill VARCHAR(100),
    PRIMARY KEY (Id_Profilo, Competenza_Skill),
    FOREIGN KEY (Id_Profilo) REFERENCES PROFILO(Id) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE SKILL (
    Competenza VARCHAR(100) PRIMARY KEY
) ENGINE = "INNODB";

CREATE TABLE POSSIEDE (
    Livello INT,
    Email_Utente VARCHAR(255),
    Competenza_Skill VARCHAR(100),
    PRIMARY KEY (Email_Utente, Competenza_Skill),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email) ON DELETE CASCADE,
    FOREIGN KEY (Competenza_Skill) REFERENCES SKILL(Competenza) ON DELETE CASCADE
) ENGINE = "INNODB";

CREATE TABLE CANDIDATURA (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Stato VARCHAR(50),
    Email_Utente VARCHAR(255),
    Id_Profilo INT,
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email) ON DELETE CASCADE,
    FOREIGN KEY (Id_Profilo) REFERENCES PROFILO(Id) ON DELETE CASCADE
) ENGINE = "INNODB";

DELIMITER $
CREATE PROCEDURE InserisciCompetenza(IN nomeCompetenza VARCHAR(100))
BEGIN
    INSERT INTO SKILL (Competenza) VALUES (nomeCompetenza);
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE EliminaCompetenza(IN nomeCompetenza VARCHAR(100))
BEGIN
    DELETE FROM SKILL WHERE Competenza = nomeCompetenza;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE OttieniCompetenze()
BEGIN
    SELECT * FROM SKILL;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE InserisciComponente(
    IN p_Nome VARCHAR(100),
    IN p_Nome_ProgettoHardware VARCHAR(100),
    IN p_Descrizione VARCHAR(100),
    IN p_Prezzo DOUBLE,
    IN p_Quantita INT
)
BEGIN
    IF p_Quantita > 0 THEN
        INSERT INTO COMPONENTE (Nome, Nome_ProgettoHardware, Descrizione, Prezzo, Quantità)
        VALUES (p_Nome, p_Nome_ProgettoHardware, p_Descrizione, p_Prezzo, p_Quantita);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantità deve essere un intero maggiore di 0';
    END IF;
END;
$
DELIMITER ;


DELIMITER $
CREATE PROCEDURE OttieniComponentiPerProgetto(IN p_Nome_ProgettoHardware VARCHAR(100))
BEGIN
    SELECT * 
    FROM COMPONENTE
    WHERE Nome_ProgettoHardware = p_Nome_ProgettoHardware;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE OttieniProfiliPerProgetto(IN p_Nome_ProgettoSoftware VARCHAR(100))
BEGIN
    SELECT * 
    FROM PROFILO, RICHIEDE
    WHERE Nome_ProgettoSoftware = p_Nome_ProgettoSoftware AND Id_Profilo = Id;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE EliminaComponente(
    IN p_Nome_Componente VARCHAR(100),
    IN p_Nome_ProgettoHardware VARCHAR(100)
)
BEGIN
    DELETE FROM COMPONENTE
    WHERE Nome_ProgettoHardware = p_Nome_ProgettoHardware
    AND Nome = p_Nome_Componente;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE EliminaProfilo(
    IN p_Id_Profilo INT
)
BEGIN
    DELETE FROM RICHIEDE WHERE Id_Profilo = p_Id_Profilo;
    DELETE FROM PROFILO WHERE Id = p_Id_Profilo;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE RegisterUser (
    IN p_Email VARCHAR(255), 
    IN p_Nickname VARCHAR(100), 
    IN p_Password VARCHAR(255), 
    IN p_Luogo VARCHAR(100), 
    IN p_Anno YEAR, 
    IN p_Nome VARCHAR(100), 
    IN p_Cognome VARCHAR(100)
)
BEGIN
    INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome)
    VALUES (p_Email, p_Nickname, p_Password, p_Luogo, p_Anno, p_Nome, p_Cognome);
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE AuthenticateUser (IN p_Email VARCHAR(255), IN p_Password VARCHAR(255))
BEGIN
    SELECT *
    FROM UTENTE
    WHERE Email = p_Email AND Password = p_Password;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE InsertUserSkill (IN p_Email_Utente VARCHAR(255), IN p_Competenza_Skill VARCHAR(100), IN p_Livello INT)
BEGIN
    INSERT INTO POSSIEDE (Email_Utente, Competenza_Skill, Livello)
    VALUES (p_Email_Utente, p_Competenza_Skill, p_Livello);
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GetAvailableProjects ()
BEGIN
    SELECT *
    FROM PROGETTO
    ORDER BY Data_Inserimento DESC;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE FinanceProject (IN p_Email_Utente VARCHAR(255), IN p_Importo DECIMAL(10,2), IN p_Nome_Progetto VARCHAR(100), 
                                  IN p_Codice_Reward VARCHAR(50))
BEGIN
    INSERT INTO FINANZIAMENTO (Data, Importo, Email_Utente, Nome_Progetto, Codice_Reward)
    VALUES (CURDATE(), p_Importo, p_Email_Utente, p_Nome_Progetto, p_Codice_Reward);
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE InsertComment (IN p_Testo TEXT, IN p_Email_Utente VARCHAR(255), 
								IN p_Nome_Progetto VARCHAR(100))
BEGIN
    INSERT INTO COMMENTO (Data, Testo, Email_Utente, Nome_Progetto)
    VALUES (CURDATE(), p_Testo, p_Email_Utente, p_Nome_Progetto);
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE InserisciProgetto(
    IN Nome VARCHAR(100), 
    IN Email_Creatore VARCHAR(255), 
    IN Descrizione TEXT, 
    IN Data_Limite DATE, 
    IN Budget DECIMAL(10,2), 
    IN Tipo VARCHAR(20)
)
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM CREATORE 
        WHERE Email_Utente = Email_Creatore
    ) AND (Tipo = 'Hardware' OR Tipo = 'Software') AND Budget > 0 THEN
        INSERT INTO PROGETTO (Nome, Email_Creatore, Descrizione, Data_Inserimento, Data_Limite, Budget, Stato, Tipo)
        VALUES (Nome, Email_Creatore, Descrizione, CURDATE(), Data_Limite, Budget, 'Aperto', Tipo);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dati non validi: controlla Email_Creatore, Tipo o Budget > 0';
    END IF;
END;
$
DELIMITER ;


DELIMITER $
CREATE PROCEDURE InserisciReward(
    IN Descrizione TEXT, 
    IN Foto VARCHAR(255), 
    IN Nome_Progetto VARCHAR(100), 
    IN Email_UtenteCreatore VARCHAR(255)
)
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM PROGETTO 
        WHERE Nome = Nome_Progetto AND Email_Creatore = Email_UtenteCreatore
    ) THEN
        INSERT INTO REWARD (Descrizione, Foto, Nome_Progetto)
        VALUES (Descrizione, Foto, Nome_Progetto);
    END IF;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE RispostaCommento (
    IN p_Testo TEXT, 
    IN p_Email_Utente VARCHAR(255), 
    IN p_Nome_Progetto VARCHAR(100), 
    IN p_IdCommento INT
)
BEGIN
	DECLARE nuovoId INT;
    IF NOT EXISTS (
        SELECT 1
        FROM COMMENTO
        WHERE Id = p_IdCommento AND Nome_Progetto = p_Nome_Progetto
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Commento non valido o non appartiene al progetto.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM RISPOSTA
        WHERE Id_Commento = p_IdCommento
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Questo commento ha già una risposta.';
    END IF;

    INSERT INTO COMMENTO (Data, Testo, Email_Utente, Nome_Progetto)
    VALUES (CURDATE(), p_Testo, p_Email_Utente, p_Nome_Progetto);

    SET nuovoId = LAST_INSERT_ID();

    INSERT INTO RISPOSTA (Id_Commento, Id_Risposta)
    VALUES (p_IdCommento, nuovoId);
END;
$ DELIMITER ;


DELIMITER $
CREATE PROCEDURE InserisciProfiloRichiede(
    IN Nome VARCHAR(100), 
    IN Nome_ProgettoSoftware VARCHAR(100), 
    IN Email_Creatore VARCHAR(255), 
    IN Livello INT, 
    IN Competenza_Skill VARCHAR(100)
)
BEGIN
    DECLARE CreatorePresente INT DEFAULT 0;
    DECLARE ProgettoPresente INT DEFAULT 0;
    DECLARE TipoProgetto VARCHAR(20);
    DECLARE nuovoIdProfilo INT;

    SET CreatorePresente = (SELECT COUNT(*) FROM CREATORE WHERE Email_Utente = Email_Creatore);
    SET ProgettoPresente = (SELECT COUNT(*) FROM PROGETTO WHERE Nome = Nome_ProgettoSoftware);
    SET TipoProgetto = (SELECT Tipo FROM PROGETTO WHERE Nome = Nome_ProgettoSoftware);

    /*IF (CreatorePresente > 0) AND (ProgettoPresente > 0) AND (TipoProgetto = 'Software') THEN*/
        INSERT INTO PROFILO (Nome, Nome_ProgettoSoftware)
        VALUES (Nome, Nome_ProgettoSoftware);
        
        SET nuovoIdProfilo = LAST_INSERT_ID();
        
        INSERT INTO RICHIEDE (Livello, Id_Profilo, Competenza_Skill)
        VALUES (Livello, nuovoIdProfilo, Competenza_Skill);
    /*END IF;*/
END;
$ DELIMITER ;



DELIMITER $
CREATE PROCEDURE GestisciCandidatura(IN idCandidatura INT, IN nuovoStato VARCHAR(50))
BEGIN
    DECLARE idProfilo INT;

    -- Recupera l'ID del profilo associato alla candidatura
    SELECT Id_Profilo INTO idProfilo
    FROM CANDIDATURA
    WHERE Id = idCandidatura;

    -- Aggiorna lo stato della candidatura specificata
    UPDATE CANDIDATURA
    SET Stato = nuovoStato
    WHERE Id = idCandidatura;

    IF nuovoStato = 'Accettata' THEN
        UPDATE CANDIDATURA
        SET Stato = 'Rifiutata'
        WHERE Id_Profilo = (SELECT Id_Profilo FROM CANDIDATURA WHERE Id = idCandidatura) AND Id != idCandidatura;
    END IF;
END;
$ DELIMITER ;

DELIMITER $
CREATE TRIGGER aggiorna_profilo_creatore
AFTER INSERT ON PROGETTO
FOR EACH ROW
BEGIN
    UPDATE CREATORE
    SET Nr_progetti = Nr_progetti + 1
    WHERE Email_Utente = NEW.Email_Creatore;

    UPDATE CREATORE c
    SET Affidabilità = (
        SELECT ROUND(COUNT(DISTINCT p.Nome) / c.Nr_progetti, 2)
        FROM PROGETTO p
        JOIN FINANZIAMENTO f ON p.Nome = f.Nome_Progetto
        WHERE p.Email_Creatore = c.Email_Utente
    )
    WHERE c.Email_Utente = NEW.Email_Creatore;
END;
$ DELIMITER ;

DELIMITER $
CREATE TRIGGER aggiorna_affidabilita_creazione
AFTER INSERT ON PROGETTO
FOR EACH ROW
BEGIN
    UPDATE CREATORE
    SET Affidabilità = ROUND(Nr_progetti / (Nr_progetti + 1), 2)
    WHERE Email_Utente = NEW.Email_Creatore;
END;
$ DELIMITER ;

DELIMITER $
CREATE TRIGGER aggiorna_affidabilita_finanziamento
AFTER INSERT ON FINANZIAMENTO
FOR EACH ROW
BEGIN
    DECLARE totaleFinanziamenti INT;
    DECLARE totaleProgetti INT;

    SELECT COUNT(*) INTO totaleFinanziamenti
    FROM FINANZIAMENTO F
    JOIN PROGETTO P ON F.Nome_Progetto = P.Nome
    WHERE P.Email_Creatore = (SELECT Email_Creatore FROM PROGETTO WHERE Nome = NEW.Nome_Progetto LIMIT 1);

    SELECT Nr_progetti INTO totaleProgetti
    FROM CREATORE
    WHERE Email_Utente = (SELECT Email_Creatore FROM PROGETTO WHERE Nome = NEW.Nome_Progetto LIMIT 1);

    UPDATE CREATORE
    SET Affidabilità = ROUND(totaleFinanziamenti / totaleProgetti, 2)
    WHERE Email_Utente = (SELECT Email_Creatore FROM PROGETTO WHERE Nome = NEW.Nome_Progetto LIMIT 1);
END;
$ DELIMITER ;

DELIMITER $
CREATE TRIGGER chiudi_progetto_per_budget
AFTER INSERT ON FINANZIAMENTO
FOR EACH ROW
BEGIN
    IF (
        SELECT SUM(Importo)
        FROM FINANZIAMENTO
        WHERE Nome_Progetto = NEW.Nome_Progetto
    ) >= (
        SELECT Budget
        FROM PROGETTO
        WHERE Nome = NEW.Nome_Progetto
    ) THEN
        UPDATE PROGETTO
        SET Stato = 'Chiuso'
        WHERE Nome = NEW.Nome_Progetto;
    END IF;
END;
$ DELIMITER ;

SET GLOBAL event_scheduler = ON;

DELIMITER $
CREATE EVENT chiusura_progetti_scaduti
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE PROGETTO
    SET Stato = 'Chiuso'
    WHERE Data_Limite < CURDATE() AND Stato != 'Chiuso';
END;
$ DELIMITER ;


CREATE VIEW classifica_affidabilita_creatori AS
SELECT u.Nickname, c.Affidabilità
FROM CREATORE c
JOIN UTENTE u ON c.Email_Utente = u.Email
ORDER BY c.Affidabilità DESC LIMIT 3;

DELIMITER $
CREATE PROCEDURE OttieniListaAffidabilità ()
BEGIN
    SELECT *
    FROM classifica_affidabilita_creatori;
END;
$ DELIMITER ;

CREATE VIEW progetti_quasi_completi AS
SELECT p.Nome,
       (p.Budget - COALESCE(SUM(f.Importo), 0)) AS Differenza, p.Budget
FROM PROGETTO p
LEFT JOIN FINANZIAMENTO f ON p.Nome = f.Nome_Progetto
WHERE p.Stato = 'Aperto'
GROUP BY p.Nome, p.Budget
ORDER BY Differenza ASC LIMIT 3;

DELIMITER $
CREATE PROCEDURE OttieniListaProgetti ()
BEGIN
    SELECT *
    FROM progetti_quasi_completi;
END;
$ DELIMITER ;

CREATE VIEW classifica_finanziatori AS
SELECT u.Nickname,
       SUM(f.Importo) AS Totale
FROM FINANZIAMENTO f
JOIN UTENTE u ON f.Email_Utente = u.Email
GROUP BY u.Nickname
ORDER BY Totale DESC LIMIT 3;

DELIMITER $
CREATE PROCEDURE OttieniListaFinanziamenti ()
BEGIN
    SELECT *
    FROM classifica_finanziatori;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE OttieniCandidaturePerProfilo(IN idProfilo INT)
BEGIN
    SELECT C.Id, C.Stato, C.Email_Utente, U.Nickname
    FROM CANDIDATURA C
    JOIN UTENTE U ON C.Email_Utente = U.Email
    WHERE C.Id_Profilo = idProfilo;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE OttieniProgettiDisponibili()
BEGIN
    SELECT Nome, Descrizione, Budget, Data_Limite, Tipo, Stato
    FROM PROGETTO
    WHERE Stato = 'Aperto'
    ORDER BY Data_Inserimento DESC;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE RimuoviSkillUtente(
    IN p_Email VARCHAR(255),
    IN p_Competenza VARCHAR(100)
)
BEGIN
    DELETE FROM POSSIEDE
    WHERE Email_Utente = p_Email AND Competenza_Skill = p_Competenza;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE AggiungiSkillUtente(
    IN p_Email VARCHAR(255),
    IN p_Competenza VARCHAR(100),
    IN p_Livello INT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM SKILL
        WHERE Competenza = p_Competenza
    ) THEN
        INSERT INTO POSSIEDE (Email_Utente, Competenza_Skill, Livello)
        VALUES (p_Email, p_Competenza, p_Livello)
        ON DUPLICATE KEY UPDATE Livello = p_Livello;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La competenza non esiste.';
    END IF;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GetCommentiProgetto(IN p_Nome_Progetto VARCHAR(100))
BEGIN
    SELECT c.Id, c.Data, c.Testo, c.Email_Utente
    FROM COMMENTO c
    WHERE c.Nome_Progetto = p_Nome_Progetto
      AND NOT EXISTS (
         SELECT 1
         FROM RISPOSTA r
         WHERE Id_Risposta = c.Id
       )
    ORDER BY c.Data DESC;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GetRispostaCommento(IN p_Id_Commento INT)
BEGIN
    SELECT c.Testo, c.Data, c.Email_Utente
    FROM COMMENTO c
    INNER JOIN RISPOSTA r ON c.Id = r.Id_Risposta
    WHERE r.Id_Commento = p_Id_Commento;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GetRewardsProgetto(IN p_Nome_Progetto VARCHAR(100))
BEGIN
    SELECT Codice, Descrizione, Foto
    FROM REWARD
    WHERE Nome_Progetto = p_Nome_Progetto;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GetFotoProgetto(IN p_Nome_Progetto VARCHAR(100))
BEGIN
    SELECT Valore
    FROM FOTO
    WHERE Nome_Progetto = p_Nome_Progetto;
END;
$ DELIMITER ;

DELIMITER $

CREATE PROCEDURE InsertCandidature (
    IN p_Email_Utente VARCHAR(255),
    IN p_Id_Profilo INT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM CANDIDATURA
        WHERE Id_Profilo = p_Id_Profilo AND Stato = 'Accettata'
    ) THEN
        SIGNAL SQLSTATE '45001'
        SET MESSAGE_TEXT = 'Un\'altra candidatura è già stata accettata per questo profilo.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM CANDIDATURA
        WHERE Id_Profilo = p_Id_Profilo AND Email_Utente = p_Email_Utente
    ) THEN
        SIGNAL SQLSTATE '45002'
        SET MESSAGE_TEXT = 'Hai già inviato una candidatura per questo profilo.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM RICHIEDE r
        LEFT JOIN POSSIEDE p
        ON r.Competenza_Skill = p.Competenza_Skill AND p.Email_Utente = p_Email_Utente
        WHERE r.Id_Profilo = p_Id_Profilo
        AND (p.Livello IS NULL OR p.Livello < r.Livello)
    ) THEN
        SIGNAL SQLSTATE '45003'
        SET MESSAGE_TEXT = 'Non possiedi le competenze richieste o il livello minimo per candidarti a questo profilo.';
    END IF;

    INSERT INTO CANDIDATURA (Stato, Email_Utente, Id_Profilo)
    VALUES ("In attesa", p_Email_Utente, p_Id_Profilo);
END
$ DELIMITER ;



