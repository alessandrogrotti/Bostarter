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
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email)
) ENGINE = "INNODB";

CREATE TABLE AMMINISTRATORE (
    Email_Utente VARCHAR(255) PRIMARY KEY,
    Codice_Sicurezza VARCHAR(50) NOT NULL,
    FOREIGN KEY (Email_Utente) REFERENCES UTENTE(Email)
) ENGINE = "INNODB";

CREATE TABLE PROGETTO (
    Nome VARCHAR(100) PRIMARY KEY,
    Email_Creatore VARCHAR(255),
    Descrizione TEXT,
    Data_Inserimento DATE,
    Data_Limite DATE,
    Budget DECIMAL(10, 2),
    Stato VARCHAR(50) DEFAULT 'Aperto',
    Tipo ENUM ('Hardware', 'Software'),
    FOREIGN KEY (Email_Creatore) REFERENCES CREATORE(Email_Utente)
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




