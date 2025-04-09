-- 1.1 -- 
DELIMITER $
CREATE PROCEDURE RegisterUser (IN p_Email VARCHAR(255), IN p_Nickname VARCHAR(100), IN p_Password VARCHAR(30), IN p_Luogo VARCHAR(100), IN p_Anno YEAR, IN p_Nome VARCHAR(100), IN p_Cognome VARCHAR(100))
BEGIN
    INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome)
    VALUES (p_Email, p_Nickname, p_Password, p_Luogo, p_Anno, p_Nome, p_Cognome);
END;
$ DELIMITER ;


-- 1.2 -- 
DELIMITER $
CREATE PROCEDURE AuthenticateUser (IN p_Email VARCHAR(255), IN p_Password VARCHAR(30))
BEGIN
	SELECT *
    FROM UTENTE
    WHERE Email = p_Email AND Password = p_Password;
END;
$ DELIMITER ;


-- 2 --
DELIMITER $
CREATE PROCEDURE InsertUserSkill (IN p_Email_Utente VARCHAR(255), IN p_Competenza_Skill VARCHAR(100), IN p_Livello INT)
BEGIN
    INSERT INTO POSSIEDE (Email_Utente, Competenza_Skill, Livello)
    VALUES (p_Email_Utente, p_Competenza_Skill, p_Livello);
END;
$ DELIMITER ;


-- 3 --
DELIMITER $
CREATE PROCEDURE GetAvailableProjects ()
BEGIN
    SELECT Nome, Email_Creatore, Descrizione, Data_Inserimento, Data_Limite, Budget, Stato, Tipo
    FROM PROGETTO
    WHERE Stato = 'aperto'
    ORDER BY Data_Inserimento DESC;
END;
DELIMITER $ ;

-- 4 & 5 --
DELIMITER $
CREATE PROCEDURE FinanceProject (IN p_Email_Utente VARCHAR(255), IN p_Importo DECIMAL(10,2), IN p_Nome_Progetto VARCHAR(100), IN p_Codice_Reward VARCHAR(50) DEFAULT NULL)
BEGIN
    INSERT INTO FINANZIAMENTO (Data, Importo, Email_Utente, Nome_Progetto, Codice_Reward)
    VALUES (CURDATE(), p_Importo, p_Email_Utente, p_Nome_Progetto, p_Codice_Reward);
END;
DELIMITER $ ;
-- La pagina web/PHP esegue una query per recuperare le reward disponibili e l'utente sceglie quella che desidera. -- 
-- Il form include il codice della reward e viene passato come parametro alla stored procedure "FinanceProject". -- 
-- In questo scenario, la stored procedure non ha bisogno di eseguire un SELECT perché riceve già il valore. -- 


-- 6 --
DELIMITER $
CREATE PROCEDURE InsertComment (IN p_Testo TEXT, IN p_Email_Utente VARCHAR(255), IN p_Email_Creatore VARCHAR(255), IN p_Nome_Progetto VARCHAR(100))
BEGIN
    INSERT INTO COMMENTO (Data, Testo, Email_Utente, Email_Creatore, Nome_Progetto)
    VALUES (CURDATE(), p_Testo, p_Email_Utente, p_Email_Creatore, p_Nome_Progetto);
END;
DELIMITER $ ;


-- 7 -- 
DELIMITER $
CREATE PROCEDURE InsertCandidature (IN p_Stato VARCHAR(50), IN p_Email_Utente VARCHAR(255), IN p_Id_Profilo INT)
BEGIN
    INSERT INTO CANDIDATURA (Stato, Email_Utente, Id_Profilo)
    VALUES (p_Stato, p_Email_Utente, p_Id_Profilo);
END;
DELIMITER $ ;


