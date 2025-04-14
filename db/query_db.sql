/*
-- Crea il database bostarter_db se non esiste
CREATE DATABASE IF NOT EXISTS bostarter_db;

-- Seleziona il database
USE bostarter_db;

-- Elimina la tabella users se esiste già (opzionale, utile per test)
DROP TABLE IF EXISTS users;

-- Crea la tabella users con id e name
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Assicura che l'ID parta sempre da 1
ALTER TABLE users AUTO_INCREMENT = 1;

-- Inserisce dati di esempio
INSERT INTO users (name) VALUES
('User1'),
('User2'),
('User3');
*/


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
    'Hardware'
),
(
    'App Gestione Spese',
    'luisa.bianchi@example.com',
    'Applicazione mobile per la gestione delle spese personali e report mensili.',
    '2025-03-20',
    '2025-06-15',
    2000.00,
    'Aperto',
    'Software'
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
);

-- Trigger per l'aggiornamento automatico del numero dei progetti di un creatore --
DELIMITER $$
CREATE TRIGGER aggiorna_nr_progetti
AFTER INSERT ON PROGETTO
FOR EACH ROW
BEGIN
    UPDATE CREATORE
    SET Nr_progetti = Nr_progetti + 1
    WHERE Email_Utente = NEW.Email_Creatore;
END $$
DELIMITER ;

-- Reward relative a un progetto -- 
INSERT INTO REWARD (Codice, Descrizione, Foto, Nome_Progetto) VALUES
('RWD-SDS-1', 'Ringraziamento speciale sul sito web e menzione tra i supporter', 'img/rewards/rwd-sds-1.jpg', 'Sistema Domotico Smart'),
('RWD-SDS-2', 'Accesso anticipato alla versione beta e demo esclusiva', 'img/rewards/rwd-sds-2.jpg', 'Sistema Domotico Smart'),
('RWD-SDS-3', 'Installazione gratuita del sistema per i primi 50 finanziatori', 'img/rewards/rwd-sds-3.jpg', 'Sistema Domotico Smart');
