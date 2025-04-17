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
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome)
) ENGINE = "INNODB";

CREATE TABLE COMMENTO (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Data DATE,
    Testo TEXT,
    Email_Utente VARCHAR(255),
    Email_Creatore VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email),
    FOREIGN KEY (Email_Creatore) REFERENCES CREATORE(Email_Utente),
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome)
) ENGINE = "INNODB";

CREATE TABLE REWARD (
    Codice VARCHAR(50) PRIMARY KEY,
    Descrizione TEXT,
    Foto VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome)
) ENGINE = "INNODB";

CREATE TABLE FINANZIAMENTO (
    Data DATE,
    Importo DECIMAL(10, 2),
    Email_Utente VARCHAR(255),
    Nome_Progetto VARCHAR(100),
    Codice_Reward VARCHAR(50),
    PRIMARY KEY (Data, Email_Utente, Nome_Progetto),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email),
    FOREIGN KEY (Nome_Progetto) REFERENCES PROGETTO(Nome),
    FOREIGN KEY (Codice_Reward) REFERENCES REWARD(Codice)
) ENGINE = "INNODB";

CREATE TABLE COMPOSIZIONE (
    Nome_ProgettoHardware VARCHAR(100),
    Nome_Componente VARCHAR(100),
    PRIMARY KEY (Nome_ProgettoHardware, Nome_Componente),
    FOREIGN KEY (Nome_ProgettoHardware) REFERENCES PROGETTO(Nome)
) ENGINE = "INNODB";

CREATE TABLE PROFILO (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(100),
    Nome_ProgettoSoftware VARCHAR(100),
    FOREIGN KEY (Nome_ProgettoSoftware) REFERENCES PROGETTO(Nome)
) ENGINE = "INNODB";

CREATE TABLE RICHIEDE (
    Livello INT,
    Id_Profilo INT,
    Competenza_Skill VARCHAR(100),
    PRIMARY KEY (Id_Profilo, Competenza_Skill),
    FOREIGN KEY (Id_Profilo) REFERENCES PROFILO(Id)
) ENGINE = "INNODB";

CREATE TABLE SKILL (
    Competenza VARCHAR(100) PRIMARY KEY
) ENGINE = "INNODB";

CREATE TABLE POSSIEDE (
    Livello INT,
    Email_Utente VARCHAR(255),
    Competenza_Skill VARCHAR(100),
    PRIMARY KEY (Email_Utente, Competenza_Skill),
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email),
    FOREIGN KEY (Competenza_Skill) REFERENCES SKILL(Competenza)
) ENGINE = "INNODB";

CREATE TABLE CANDIDATURA (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Stato VARCHAR(50),
    Email_Utente VARCHAR(255),
    Id_Profilo INT,
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email),
    FOREIGN KEY (Id_Profilo) REFERENCES PROFILO(Id)
) ENGINE = "INNODB";

# Stored procedures per AMMINISTRATORE

DELIMITER //

CREATE PROCEDURE InserisciCompetenza(IN nomeCompetenza VARCHAR(100))
BEGIN
    INSERT INTO SKILL (Competenza) VALUES (nomeCompetenza);
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE EliminaCompetenza(IN nomeCompetenza VARCHAR(100))
BEGIN
    DELETE FROM SKILL WHERE Competenza = nomeCompetenza;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE OttieniCompetenze()
BEGIN
    SELECT * FROM SKILL;
END //

DELIMITER ;

-- 1.1 -- 
DELIMITER $$
CREATE PROCEDURE RegisterUser (IN p_Email VARCHAR(255), IN p_Nickname VARCHAR(100), IN p_Password VARCHAR(255), 
							   IN p_Luogo VARCHAR(100), IN p_Anno YEAR, IN p_Nome VARCHAR(100), IN p_Cognome VARCHAR(100))
BEGIN
    INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome)
    VALUES (p_Email, p_Nickname, p_Password, p_Luogo, p_Anno, p_Nome, p_Cognome);
END $$
DELIMITER ;


-- 1.2 -- 
DELIMITER $$
CREATE PROCEDURE AuthenticateUser (IN p_Email VARCHAR(255), IN p_Password VARCHAR(255))
BEGIN
	SELECT *
    FROM UTENTE
    WHERE Email = p_Email AND Password = p_Password;
END $$
DELIMITER ;


-- 2 --
DELIMITER $$
CREATE PROCEDURE InsertUserSkill (IN p_Email_Utente VARCHAR(255), IN p_Competenza_Skill VARCHAR(100), IN p_Livello INT)
BEGIN
    INSERT INTO POSSIEDE (Email_Utente, Competenza_Skill, Livello)
    VALUES (p_Email_Utente, p_Competenza_Skill, p_Livello);
END $$
DELIMITER ;


-- 3 --
DELIMITER $$
CREATE PROCEDURE GetAvailableProjects ()
BEGIN
    SELECT *
    FROM PROGETTO
    WHERE Stato = 'Aperto'
    ORDER BY Data_Inserimento DESC;
END $$
DELIMITER ;


-- 4 & 5 --
DELIMITER $$
CREATE PROCEDURE FinanceProject (IN p_Email_Utente VARCHAR(255), IN p_Importo DECIMAL(10,2), IN p_Nome_Progetto VARCHAR(100), 
								 IN p_Codice_Reward VARCHAR(50))
BEGIN
    INSERT INTO FINANZIAMENTO (Data, Importo, Email_Utente, Nome_Progetto, Codice_Reward)
    VALUES (CURDATE(), p_Importo, p_Email_Utente, p_Nome_Progetto, p_Codice_Reward);
END $$
DELIMITER ; 
-- La pagina web/PHP esegue una query per recuperare le reward disponibili e l'utente sceglie quella che desidera. -- 
-- Il form include il codice della reward e viene passato come parametro alla stored procedure "FinanceProject". -- 
-- In questo scenario, la stored procedure non ha bisogno di eseguire un SELECT perché riceve già il valore. -- 


-- 6 --
DELIMITER $$
CREATE PROCEDURE InsertComment (IN p_Testo TEXT, IN p_Email_Utente VARCHAR(255), IN p_Email_Creatore VARCHAR(255), 
								IN p_Nome_Progetto VARCHAR(100))
BEGIN
    INSERT INTO COMMENTO (Data, Testo, Email_Utente, Email_Creatore, Nome_Progetto)
    VALUES (CURDATE(), p_Testo, p_Email_Utente, p_Email_Creatore, p_Nome_Progetto);
END $$
DELIMITER ;


-- 7 -- 
DELIMITER $$
CREATE PROCEDURE InsertCandidature (IN p_Stato VARCHAR(50), IN p_Email_Utente VARCHAR(255), IN p_Id_Profilo INT)
BEGIN
    INSERT INTO CANDIDATURA (Stato, Email_Utente, Id_Profilo)
    VALUES (p_Stato, p_Email_Utente, p_Id_Profilo);
END $$
DELIMITER ;


USE bostarter_db;

DELIMITER $
CREATE PROCEDURE InserisciProgetto(IN Nome VARCHAR(100), IN Email_Creatore VARCHAR(255), IN Descrizione TEXT, IN Data_Inserimento DATE, IN Data_Limite DATE, IN Budget DECIMAL(10,2), IN Stato VARCHAR(50), IN Tipo VARCHAR(20))
BEGIN
    DECLARE CreatorePresente INT DEFAULT 0;
    
    SET CreatorePresente = (SELECT COUNT(*) FROM CREATORE WHERE (Email_Utente = Email_Creatore));

    IF (CreatorePresente > 0) AND (Tipo = "Hardware" OR Tipo = "Software") THEN
        INSERT INTO PROGETTO (Nome, Email_Creatore, Descrizione, Data_Inserimento, Data_Limite, Budget, Stato, Tipo)
        VALUES (Nome, Email_Creatore, Descrizione, Data_Inserimento, Data_Limite, Budget, Stato, Tipo);
        
        UPDATE CREATORE 
        SET Nr_progetti = Nr_progetti + 1
        WHERE (Email_Utente = Email_Creatore);
    END IF;
END;
$ DELIMITER ;

/*DELIMITER $
CREATE PROCEDURE InserisciReward(IN Codice VARCHAR(50), Descrizione TEXT, Foto VARCHAR(255), Nome_Progetto VARCHAR(100), Email_Creatore VARCHAR(255))
BEGIN
    DECLARE CreatorePresente INT DEFAULT 0;

    SET CreatorePresente = (SELECT COUNT(*) FROM CREATORE WHERE (Email_Utente = Email_Creatore));

    IF (CreatorePresente > 0) THEN
        INSERT INTO REWARD (Codice, Descrizione, Foto, Nome_Progetto)
        VALUES (Codice, Descrizione, Foto, Nome_Progetto);
    END IF;
END;
$ DELIMITER ;*/

DELIMITER $
CREATE PROCEDURE InserisciReward(IN Codice VARCHAR(50), IN Descrizione TEXT, IN Foto VARCHAR(255), IN Nome_Progetto VARCHAR(100), IN Email_UtenteCreatore VARCHAR(255)
)
BEGIN
    DECLARE CreatoreProgetto INT DEFAULT 0;

    SET CreatoreProgetto = (SELECT COUNT(*) FROM PROGETTO WHERE (Nome = Nome_Progetto) AND (Email_Creatore = Email_UtenteCreatore));

    IF (CreatoreProgetto > 0) THEN
        INSERT INTO REWARD (Codice, Descrizione, Foto, Nome_Progetto)
        VALUES (Codice, Descrizione, Foto, Nome_Progetto);
    END IF;
END;
$ DELIMITER ;

-- Inserisci risposta ad un commento

DELIMITER $
CREATE PROCEDURE InserisciProfilo(IN Nome VARCHAR(100), Nome_ProgettoSoftware VARCHAR(100), Email_Creatore VARCHAR(255))
BEGIN
	DECLARE CreatorePresente INT DEFAULT 0;
    DECLARE ProgettoPresente INT DEFAULT 0;
    DECLARE TipoProgetto VARCHAR(20);

	SET CreatorePresente = (SELECT COUNT(*) FROM CREATORE WHERE (Email_Utente = Email_Creatore));
    SET ProgettoPresente = (SELECT COUNT(*) FROM PROGETTO WHERE (Nome = Nome_ProgettoSoftware));
    SET TipoProgetto = (SELECT Tipo FROM PROGETTO WHERE (Nome = Nome_ProgettoSoftware));
    
	/*SELECT CreatorePresente, ProgettoPresente, TipoProgetto;*/

    IF (CreatorePresente > 0) AND (ProgettoPresente > 0) AND (TipoProgetto = "Software") THEN
        INSERT INTO PROFILO (Nome, Nome_ProgettoSoftware)
        VALUES (Nome, Nome_ProgettoSoftware);
    END IF;
END;
$ DELIMITER ;

DELIMITER $
CREATE PROCEDURE GestisciCandidatura(IN Id_Candidatura INT,IN Nuovo_Stato VARCHAR(50),IN Email_Creatore VARCHAR(255))
BEGIN
    DECLARE ProgettoCreatore INT DEFAULT 0;
    DECLARE StatoCandidatura VARCHAR(50);
    
    SET ProgettoCreatore = (SELECT COUNT(*) 
							FROM CREATORE c
							JOIN PROGETTO p ON (p.Email_Creatore = c.Email_Utente)
							JOIN PROFILO pr ON (pr.Nome_ProgettoSoftware = p.Nome)
							JOIN CANDIDATURA ca ON (ca.Id_Profilo = pr.Id)
							WHERE (ca.Id = Id_Candidatura) AND (c.Email_Utente = Email_Creatore));
    
    SET StatoCandidatura = (SELECT Stato FROM CANDIDATURA WHERE (Id = Id_Candidatura));

    IF (ProgettoCreatore > 0) AND (StatoCandidatura= 'In attesa') THEN
        UPDATE CANDIDATURA
        SET Stato = Nuovo_Stato
        WHERE (Id = Id_Candidatura);
    END IF;
END;
$ DELIMITER ;












