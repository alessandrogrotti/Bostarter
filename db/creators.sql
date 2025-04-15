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

-- Aggiunge un utente nella tabella UTENTE
INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome) 
VALUES ('creatore@esempio.com', 'CreatoreNickname', 'password123', 'Milano', 1990, 'Mario', 'Rossi');

-- Aggiunge il creatore nella tabella CREATORE
INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) 
VALUES ('creatore@esempio.com', 0, 4.5);

-- Aggiunge un utente nella tabella UTENTE
INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome) 
VALUES ('secondocreatore@esempio.com', 'SecondoCreatoreNickname', 'password1234', 'Milano', 1990, 'Maria', 'Bianchi');

-- Aggiunge il creatore nella tabella CREATORE
INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) 
VALUES ('secondocreatore@esempio.com', 0, 4.5);

-- Chiamata alla procedura per inserire un progetto
CALL InserisciProgetto(
    'Nuovo Progetto',                  -- Nome del progetto
    'creatore@esempio.com',             -- Email del creatore (deve essere un creatore valido)
    'Descrizione del progetto...',     -- Descrizione
    '2025-04-12',                       -- Data di inserimento
    '2025-12-31',                       -- Data limite
    5000.00,                            -- Budget
    'Attivo',                           -- Stato del progetto
    'Software'                          -- Tipo del progetto (può essere "Hardware" o "Software")
);

-- Chiamata alla procedura per inserire una reward (con un creatore valido)
CALL InserisciReward(
    'R001',                               -- Codice reward
    'Descrizione della reward...',         -- Descrizione reward
    'foto_rewad.png',                      -- Foto (path)
    'Nuovo Progetto',                    -- Nome del progetto (inserito con InserisciProgetto)
    'creatore@esempio.com'                 -- Email del creatore
);

-- Chiamata alla procedura per inserire un profilo (solo progetti software)

INSERT INTO PROFILO (Nome, Nome_ProgettoSoftware)
VALUES ('Backend Developer', 'Nuovo Progetto');

INSERT INTO CANDIDATURA (Stato, Email_Utente, Id_Profilo)
VALUES ('In attesa', 'creatore@esempio.com', 1);

-- Chiamara alla procedura per accettare o meno una candidatura
CALL GestisciCandidatura(1, 'Accettata', 'creatore@esempio.com');

-- Chiamata alla procedura per inserire una reward (con un creatore valido)
CALL InserisciProfilo(
    'Backend developer',
    'Nuovo Progetto',
    'creatore@esempio.com'
);










