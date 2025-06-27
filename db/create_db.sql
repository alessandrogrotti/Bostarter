-- Pulizia precedente db

DROP DATABASE IF EXISTS bostarter_db;
CREATE DATABASE IF NOT EXISTS bostarter_db;
USE bostarter_db;

-- Creazione delle tabelle
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
    Id_Commento INT PRIMARY KEY,
    Id_Risposta INT,
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

-- Stored procedure
DELIMITER $
CREATE PROCEDURE InserisciCompetenza(IN nomeCompetenza VARCHAR(100))
BEGIN
    INSERT INTO SKILL (Competenza) VALUES (nomeCompetenza);
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE EliminaCompetenza(IN Nome_Competenza VARCHAR(100))
BEGIN
    DELETE FROM SKILL WHERE Competenza = Nome_Competenza;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniCompetenze()
BEGIN
    SELECT * FROM SKILL;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE InserisciComponente(
    IN Nome VARCHAR(100),
    IN Nome_ProgettoHardware VARCHAR(100),
    IN Descrizione VARCHAR(100),
    IN Prezzo DOUBLE,
    IN Quantita INT
)
BEGIN
    IF Quantita > 0 THEN
        INSERT INTO COMPONENTE (Nome, Nome_ProgettoHardware, Descrizione, Prezzo, Quantità)
        VALUES (Nome, Nome_ProgettoHardware, Descrizione, Prezzo, Quantita);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La quantità deve essere un intero maggiore di 0';
    END IF;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniComponentiPerProgetto(IN Nome_ProgettoHardware VARCHAR(100))
BEGIN
    SELECT * 
    FROM COMPONENTE
    WHERE Nome_ProgettoHardware = Nome_ProgettoHardware;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniProfiliPerProgetto(IN Nome_ProgettoSoftware VARCHAR(100))
BEGIN
    SELECT * 
    FROM PROFILO, RICHIEDE
    WHERE Nome_ProgettoSoftware = Nome_ProgettoSoftware AND Id_Profilo = Id;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE EliminaComponente(
    IN Nome_Componente VARCHAR(100),
    IN Nome_ProgettoHardware VARCHAR(100)
)
BEGIN
    DELETE FROM COMPONENTE
    WHERE Nome_ProgettoHardware = Nome_ProgettoHardware
    AND Nome = Nome_Componente;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE EliminaProfilo(IN Id_Profilo INT)
BEGIN
    DELETE FROM PROFILO WHERE Id = Id_Profilo;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE RegistraUtente (
    IN Email VARCHAR(255), 
    IN Nickname VARCHAR(100), 
    IN in_Password VARCHAR(255), 
    IN Luogo VARCHAR(100), 
    IN Anno YEAR, 
    IN Nome VARCHAR(100), 
    IN Cognome VARCHAR(100)
)
BEGIN
    INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome)
    VALUES (Email, Nickname, in_Password, Luogo, Anno, Nome, Cognome);
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE AggiungiSkillUtente(
    IN Email VARCHAR(255),
    IN Competenza VARCHAR(100),
    IN Livello INT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM SKILL
        WHERE Competenza = Competenza
    ) THEN
        INSERT INTO POSSIEDE (Email_Utente, Competenza_Skill, Livello)
        VALUES (Email, Competenza, Livello)
        ON DUPLICATE KEY UPDATE Livello = Livello;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La competenza non esiste';
    END IF;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniProgettiDisponibili()
BEGIN
    SELECT *
    FROM PROGETTO
    ORDER BY Data_Inserimento DESC;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE FinanziaProgetto(
	IN Email_Utente VARCHAR(255), 
    IN Importo DECIMAL(10,2), 
    IN Nome_Progetto VARCHAR(100), 
	IN Codice_Reward VARCHAR(50)
)
BEGIN
    INSERT INTO FINANZIAMENTO (Data, Importo, Email_Utente, Nome_Progetto, Codice_Reward)
    VALUES (CURDATE(), Importo, Email_Utente, Nome_Progetto, Codice_Reward);
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE InserisciCommento(
	IN Testo TEXT, 
    IN Email_Utente VARCHAR(255), 
	IN Nome_Progetto VARCHAR(100)
)
BEGIN
    INSERT INTO COMMENTO (Data, Testo, Email_Utente, Nome_Progetto)
    VALUES (CURDATE(), Testo, Email_Utente, Nome_Progetto);
END;
$ DELIMITER;

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
    ) AND (Tipo = 'Hardware' OR Tipo = 'Software') AND (Budget > 0) AND (Data_Limite > CURDATE()) THEN
        INSERT INTO PROGETTO (Nome, Email_Creatore, Descrizione, Data_Inserimento, Data_Limite, Budget, Stato, Tipo)
        VALUES (Nome, Email_Creatore, Descrizione, CURDATE(), Data_Limite, Budget, 'Aperto', Tipo);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Dati non validi: controlla Email, Tipo o Budget (maggiore di zero)';
    END IF;
END;
$ DELIMITER;

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
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE RispostaCommento(
    IN Testo TEXT, 
    IN Email_Utente VARCHAR(255), 
    IN Nome_Progetto VARCHAR(100), 
    IN Id_Commento INT
)
BEGIN
	DECLARE nuovoId INT;
    IF NOT EXISTS (
        SELECT 1
        FROM COMMENTO
        WHERE Id = Id_Commento AND Nome_Progetto = Nome_Progetto
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Commento non valido o non appartiene al progetto.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM RISPOSTA as r
        WHERE r.Id_Commento = Id_Commento
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Questo commento ha già una risposta.';
    END IF;

    INSERT INTO COMMENTO (Data, Testo, Email_Utente, Nome_Progetto)
    VALUES (CURDATE(), Testo, Email_Utente, Nome_Progetto);

    SET nuovoId = LAST_INSERT_ID();

    INSERT INTO RISPOSTA (Id_Commento, Id_Risposta)
    VALUES (Id_Commento, nuovoId);
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniCandidaturePerProfilo(IN idProfilo INT)
BEGIN
    SELECT Id, Stato, Email_Utente, Nickname
    FROM CANDIDATURA, UTENTE
    WHERE Id_Profilo = idProfilo AND Email_Utente = Email;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE RimuoviSkillUtente(
    IN Email VARCHAR(255),
    IN Competenza VARCHAR(100)
)
BEGIN
    DELETE FROM POSSIEDE
    WHERE Email_Utente = Email AND Competenza_Skill = Competenza;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniCommentiProgetto(IN NomeProgetto VARCHAR(100))
BEGIN
    SELECT Id, Data, Testo, Email_Utente
    FROM COMMENTO 
    WHERE Nome_Progetto = NomeProgetto
      AND NOT EXISTS (
         SELECT 1
         FROM RISPOSTA 
         WHERE Id_Risposta = Id
       )
    ORDER BY Data DESC;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniRispostaCommento(IN IdCommento INT)
BEGIN
    SELECT Testo, Data, Email_Utente
    FROM COMMENTO, RISPOSTA
    WHERE Id_Commento = IdCommento AND Id = Id_Risposta;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniRewardProgetto(IN NomeProgetto VARCHAR(100))
BEGIN
    SELECT Codice, Descrizione, Foto
    FROM REWARD
    WHERE Nome_Progetto = NomeProgetto;
END;
$ DELIMITER;

DELIMITER $
CREATE PROCEDURE OttieniFotoProgetto(IN NomeProgetto VARCHAR(100))
BEGIN
    SELECT Valore
    FROM FOTO
    WHERE Nome_Progetto = NomeProgetto;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE InserisciCandidatura (
    IN EmailUtente VARCHAR(255),
    IN IdProfilo INT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM CANDIDATURA
        WHERE Id_Profilo = IdProfilo AND Stato = 'Accettata'
    ) THEN
        SIGNAL SQLSTATE '45001'
        SET MESSAGE_TEXT = 'Un\'altra candidatura è già stata accettata per questo profilo.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM CANDIDATURA
        WHERE Id_Profilo = IdProfilo AND Email_Utente = EmailUtente
    ) THEN
        SIGNAL SQLSTATE '45002'
        SET MESSAGE_TEXT = 'Hai già inviato una candidatura per questo profilo.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM RICHIEDE as r, POSSIEDE as p
        WHERE r.Id_Profilo = p_Id_Profilo AND r.Competenza_Skill = p.Competenza_Skill AND p.Email_Utente = EmailUtente
        AND (p.Livello < r.Livello)
    ) THEN
        SIGNAL SQLSTATE '45003'
        SET MESSAGE_TEXT = 'Non possiedi le competenze richieste o il livello minimo per candidarti a questo profilo.';
    END IF;

    INSERT INTO CANDIDATURA (Stato, Email_Utente, Id_Profilo)
    VALUES ("In attesa", p_Email_Utente, p_Id_Profilo);
END
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

	INSERT INTO PROFILO (Nome, Nome_ProgettoSoftware)
	VALUES (Nome, Nome_ProgettoSoftware);
	
	SET nuovoIdProfilo = LAST_INSERT_ID();
	
	INSERT INTO RICHIEDE (Livello, Id_Profilo, Competenza_Skill)
	VALUES (Livello, nuovoIdProfilo, Competenza_Skill);

END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE OttieniListaProgetti ()
BEGIN
    SELECT *
    FROM progetti_quasi_completi;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE OttieniListaAffidabilità ()
BEGIN
    SELECT *
    FROM classifica_affidabilita_creatori;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GestisciCandidatura(IN idCandidatura INT, IN nuovoStato VARCHAR(50))
BEGIN
    DECLARE idProfilo INT;

    SELECT Id_Profilo INTO idProfilo
    FROM CANDIDATURA
    WHERE Id = idCandidatura;

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

-- Trigger

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

-- Evento

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

-- Viste

CREATE VIEW classifica_affidabilita_creatori AS
SELECT u.Nickname, c.Affidabilità
FROM CREATORE c
JOIN UTENTE u ON c.Email_Utente = u.Email
ORDER BY c.Affidabilità DESC LIMIT 3;

CREATE VIEW progetti_quasi_completi AS
SELECT p.Nome,
       (p.Budget - COALESCE(SUM(f.Importo), 0)) AS Differenza, p.Budget
FROM PROGETTO p
LEFT JOIN FINANZIAMENTO f ON p.Nome = f.Nome_Progetto
WHERE p.Stato = 'Aperto'
GROUP BY p.Nome, p.Budget
ORDER BY Differenza ASC LIMIT 3;

CREATE VIEW classifica_finanziatori AS
SELECT u.Nickname,
       SUM(f.Importo) AS Totale
FROM FINANZIAMENTO f
JOIN UTENTE u ON f.Email_Utente = u.Email
GROUP BY u.Nickname
ORDER BY Totale DESC LIMIT 3;

DELIMITER $
CREATE PROCEDURE OttieniListaFinanziatori()
BEGIN
    SELECT *
    FROM classifica_finanziatori;
END;
$ DELIMITER ;
