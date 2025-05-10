-- Inserimento nella tabella UTENTE
INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome) VALUES
('mario.rossi@example.com', 'mRossi', 'pass123', 'Milano', 1990, 'Mario', 'Rossi'),
('luisa.bianchi@example.com', 'lBianchi', 'pass456', 'Roma', 1988, 'Luisa', 'Bianchi'),
('giorgio.verdi@example.com', 'gVerdi', 'pass789', 'Napoli', 1992, 'Giorgio', 'Verdi');

-- Inserimento nella tabella CREATORE
INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) VALUES
('mario.rossi@example.com', 0, 4.5),
('luisa.bianchi@example.com', 0, 4.2),
('giorgio.verdi@example.com', 0, 4.8);

-- Inserimento nella tabella PROGETTO -- 
INSERT INTO PROGETTO (
    Nome, Email_Creatore, Descrizione, Data_Inserimento, Data_Limite, Budget, Stato, Tipo
) VALUES 
(
    'Sistema Domotico Smart',
    'mario.rossi@example.com',
    'Progetto per la creazione di un sistema domotico intelligente basato su sensori IoT.',
    '2025-04-10',
    '2025-07-01',
    5000.00,
    'Aperto',
    'Software'
),
(
    'App Gestione Spese',
    'luisa.bianchi@example.com',
    'Applicazione mobile per la gestione delle spese personali e report mensili.',
    '2025-03-20',
    '2025-06-15',
    2000.00,
    'Aperto',
    'Hardware'
),
(
    'Piattaforma E-learning',
    'giorgio.verdi@example.com',
    'Sviluppo di una piattaforma online per corsi e formazione a distanza.',
    '2025-02-28',
    '2025-08-30',
    7500.00,
    'Aperto',
    'Software'
),
(
	'GreenCity Tracker',
    'giorgio.verdi@example.com',
    'Applicazione che monitora l’impatto ambientale in città, raccogliendo dati da fonti pubbliche e utenti per promuovere pratiche sostenibili.',
    '2025-04-12',
    '2025-07-30',
    3000.00,
    'Aperto',
    'Software'
);

-- Reward relative a un progetto -- 
INSERT INTO REWARD (Codice, Descrizione, Foto, Nome_Progetto) VALUES
('RWD-SDS-1', 'Ringraziamento speciale sul sito web e menzione tra i supporter', 'img/rewards/rwd-sds-1.jpg', 'Sistema Domotico Smart'),
('RWD-SDS-2', 'Accesso anticipato alla versione beta e demo esclusiva', 'img/rewards/rwd-sds-2.jpg', 'Sistema Domotico Smart'),
('RWD-SDS-3', 'Installazione gratuita del sistema per i primi 50 finanziatori', 'img/rewards/rwd-sds-3.jpg', 'Sistema Domotico Smart');

/*

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
    '2025-12-31',                       -- Data limite
    5000.00,                            -- Budget
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

*/

CALL InserisciCompetenza('Java');
CALL InserisciCompetenza('SQL');
CALL InserisciCompetenza('Teamwork');


