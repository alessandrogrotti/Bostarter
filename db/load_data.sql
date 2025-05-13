-- Utente
INSERT INTO UTENTE (Email, Nickname, Password, Luogo, Anno, Nome, Cognome) VALUES
('mario.rossi@example.com', 'mRossi', 'pass123', 'Milano', 1990, 'Mario', 'Rossi'),
('luisa.bianchi@example.com', 'lBianchi', 'pass456', 'Roma', 1988, 'Luisa', 'Bianchi'),
('giorgio.verdi@example.com', 'gVerdi', 'pass789', 'Napoli', 1992, 'Giorgio', 'Verdi');

-- Creatore
INSERT INTO CREATORE (Email_Utente, Nr_progetti, Affidabilità) VALUES
('mario.rossi@example.com', 0, 0),
('luisa.bianchi@example.com', 0, 0),
('giorgio.verdi@example.com', 0, 0);

-- Progetti 
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
    '2025-05-12',
    7500.00,
    'Chiuso',
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

-- Foto Progetti
INSERT INTO FOTO (Valore, Nome_Progetto) VALUES 
('uploads/Sistema_Domotico_Smart.jpg','Sistema Domotico Smart'),
('uploads/Sistema_Domotico_Smart_2.png','Sistema Domotico Smart'),
('uploads/app_gestione_spese.jpg','App Gestione Spese'),
('uploads/Piattaforma_EL.jpg','Piattaforma E-learning'),
('uploads/GreenCity_Tracker.jpg','GreenCity Tracker');


-- Reward
INSERT INTO REWARD (Codice, Descrizione, Foto, Nome_Progetto) VALUES
-- Sistema Domotico Smart
('RWD-SDS-1', 'Ringraziamento speciale sul sito web e menzione tra i supporter', 'uploads/reward.png', 'Sistema Domotico Smart'),
('RWD-SDS-2', 'Accesso anticipato alla versione beta e demo esclusiva', 'uploads/reward.png', 'Sistema Domotico Smart'),
('RWD-SDS-3', 'Installazione gratuita del sistema per i primi 50 finanziatori', 'uploads/reward.png', 'Sistema Domotico Smart'),

-- App Gestione Spese
('RWD-AGS-1', 'Accesso alla versione premium per 6 mesi senza costi', 'uploads/reward.png', 'App Gestione Spese'),
('RWD-AGS-2', 'Report finanziario personalizzato con analisi avanzata', 'uploads/reward.png', 'App Gestione Spese'),
('RWD-AGS-3', 'Ringraziamento con nome nella sezione supporter dell’app', 'uploads/reward.png', 'App Gestione Spese'),

-- Piattaforma E-learning
('RWD-PEL-1', 'Accesso gratuito a un corso premium a scelta', 'uploads/reward.png', 'Piattaforma E-learning'),
('RWD-PEL-2', 'Certificato digitale di sostenitore ufficiale del progetto', 'uploads/reward.png', 'Piattaforma E-learning'),
('RWD-PEL-3', 'Webinar esclusivo con i docenti e sviluppatori', 'uploads/reward.png', 'Piattaforma E-learning'),

-- GreenCity Tracker
('RWD-GCT-1', 'Badge esclusivo “Green Supporter” visibile nel profilo', 'uploads/reward.png', 'GreenCity Tracker'),
('RWD-GCT-2', 'T-shirt in cotone organico con logo del progetto', 'uploads/reward.png', 'GreenCity Tracker'),
('RWD-GCT-3', 'Possibilità di testare nuove funzionalità in anteprima', 'uploads/reward.png', 'GreenCity Tracker');

-- Competenza
INSERT INTO SKILL (Competenza) VALUES ('Java'), ('SQL'), ('Teamwork'), ('Python'), ('HTML');

-- Componenti
INSERT INTO COMPONENTE (Nome, Nome_ProgettoHardware, Descrizione, Prezzo, Quantità) VALUES
('Lettore NFC', 'App Gestione Spese', 'Dispositivo per la lettura di tag NFC per autenticazione', 35.00, 20),
('Modulo Bluetooth 5.0', 'App Gestione Spese', 'Permette la comunicazione tra app e dispositivi mobili', 12.50, 30),
('Display OLED 0.96"', 'App Gestione Spese', 'Schermo per la visualizzazione di notifiche o spese', 7.80, 15);

-- Profili
CALL InserisciProfiloRichiede('Frontend Developer', 'Sistema Domotico Smart', 'mario.rossi@example.com', 3,'HTML');
CALL InserisciProfiloRichiede('Database Managment', 'Sistema Domotico Smart', 'mario.rossi@example.com', 3,'SQL');
CALL InserisciProfiloRichiede('Collaborazione', 'App Gestione Spese', 'luisa.bianchi@example.com', 2, 'Teamwork');
CALL InserisciProfiloRichiede('Programmazione', 'GreenCity Tracker', 'giorgio.verdi@example.com', 2, 'Java');
CALL InserisciProfiloRichiede('Programmazione', 'Piattaforma E-learning','giorgio.verdi@example.com', 2, 'Java');

-- 