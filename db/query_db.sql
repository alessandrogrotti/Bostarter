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

