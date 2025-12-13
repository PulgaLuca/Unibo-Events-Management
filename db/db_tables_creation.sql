-- Creazione db
DROP DATABASE IF EXISTS unibo_matchskills_db;
CREATE DATABASE IF NOT EXISTS unibo_matchskills_db;
USE unibo_matchskills_db;

-- Impostazione standard per una migliore gestione delle tabelle
SET NAMES utf8mb4;
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- *******************************************
-- I. Entità Principali e Anagrafiche
-- *******************************************

CREATE TABLE USER (
    user_id CHAR(36) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    is_professor BOOLEAN DEFAULT FALSE,
    is_mentor BOOLEAN DEFAULT FALSE,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_accesso TIMESTAMP NULL,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB;

CREATE TABLE SKILL (
    skill_id CHAR(36) NOT NULL,
    nome VARCHAR(150) NOT NULL UNIQUE,
    categoria ENUM('Hard', 'Soft', 'Altro') NOT NULL,
    PRIMARY KEY (skill_id)
) ENGINE=InnoDB;

CREATE TABLE EVENT_TYPE (
    type_id CHAR(36) NOT NULL,
    nome VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (type_id)
) ENGINE=InnoDB;

CREATE TABLE PARTICIPATION_TYPE (
    type_id CHAR(36) NOT NULL,
    nome VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (type_id)
) ENGINE=InnoDB;

-- Tabella: TAG (per eventi)
CREATE TABLE TAG (
    tag_id CHAR(36) NOT NULL,
    nome VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (tag_id)
) ENGINE=InnoDB;

-- *******************************************
-- II. Entità di Profilazione (Solo Skill)
-- *******************************************

-- Tabella: USER_SKILL (Molti-a-Molti con attributi)
CREATE TABLE USER_SKILL (
    user_id CHAR(36) NOT NULL,
    skill_id CHAR(36) NOT NULL,
    livello TINYINT CHECK (livello BETWEEN 1 AND 5) NOT NULL,
    PRIMARY KEY (user_id, skill_id),
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES SKILL (skill_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- *******************************************
-- III. Entità Gruppi (TEAM)
-- *******************************************

CREATE TABLE TEAM (
    TEAM_id CHAR(36) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descrizione TEXT,
    `status` ENUM('In Ricerca', 'Completo', 'Inattivo') NOT NULL,
    max_partecipanti SMALLINT NOT NULL,
    min_partecipanti SMALLINT DEFAULT 1,
    mentor_id CHAR(36) NULL, -- Opzionale
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (TEAM_id),
    FOREIGN KEY (mentor_id) REFERENCES USER (user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabella: TEAM_MEMBERSHIP (gestisce i ruoli dinamici)
CREATE TABLE TEAM_MEMBERSHIP (
    TEAM_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    `role` ENUM('Lead', 'Participant') NOT NULL,
    data_join TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    stato_richiesta ENUM('Accettata', 'In Attesa', 'Rifiutata') NOT NULL,
    PRIMARY KEY (TEAM_id, user_id),
    FOREIGN KEY (TEAM_id) REFERENCES TEAM (TEAM_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE TEAM_REQUIRED_SKILL (
    TEAM_id CHAR(36) NOT NULL,
    skill_id CHAR(36) NOT NULL,
    livello_minimo_richiesto TINYINT CHECK (livello_minimo_richiesto BETWEEN 1 AND 5),
    PRIMARY KEY (TEAM_id, skill_id),
    FOREIGN KEY (TEAM_id) REFERENCES TEAM (TEAM_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES SKILL (skill_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- *******************************************
-- IV. Entità Eventi e Partecipazione
-- *******************************************

CREATE TABLE EVENT (
    event_id CHAR(36) NOT NULL,
    titolo VARCHAR(255) NOT NULL,
    descrizione TEXT,
    data_inizio DATETIME NOT NULL,
    data_fine DATETIME NULL,
    luogo VARCHAR(255),
    max_partecipanti INT NULL,
    `status` ENUM('Attivo', 'Concluso', 'Annullato', 'Bozza') NOT NULL,
    type_id CHAR(36) NOT NULL,
    participation_type_id CHAR(36) NOT NULL,
    creator_user_id CHAR(36) NULL, -- Creatore Singolo (Opzionale)
    creator_TEAM_id CHAR(36) NULL, -- Creatore Gruppo (Opzionale)
    PRIMARY KEY (event_id),
    FOREIGN KEY (type_id) REFERENCES EVENT_TYPE (type_id) ON DELETE RESTRICT,
    FOREIGN KEY (participation_type_id) REFERENCES PARTICIPATION_TYPE (type_id) ON DELETE RESTRICT,
    FOREIGN KEY (creator_user_id) REFERENCES USER (user_id) ON DELETE SET NULL,
    FOREIGN KEY (creator_TEAM_id) REFERENCES TEAM (TEAM_id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE INDEX idx_event_type ON EVENT (type_id);
CREATE INDEX idx_event_start_date ON EVENT (data_inizio);

-- Tabella: EVENT_EXTERNAL_DETAIL (1:1 con EVENT, solo per eventi esterni)
CREATE TABLE EVENT_EXTERNAL_DETAIL (
    event_id CHAR(36) NOT NULL,
    url_originale VARCHAR(512),
    requisiti TEXT,
    livello_difficolta ENUM('Basso', 'Medio', 'Alto') DEFAULT 'Medio',
    scadenza_registrazione DATE,
    PRIMARY KEY (event_id),
    FOREIGN KEY (event_id) REFERENCES EVENT (event_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabella: EVENT_TAG (Molti-a-Molti)
CREATE TABLE EVENT_TAG (
    event_id CHAR(36) NOT NULL,
    tag_id CHAR(36) NOT NULL,
    PRIMARY KEY (event_id, tag_id),
    FOREIGN KEY (event_id) REFERENCES EVENT (event_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES TAG (tag_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabella: EVENT_REQUIRED_SKILL (Molti-a-Molti)
CREATE TABLE EVENT_REQUIRED_SKILL (
    event_id CHAR(36) NOT NULL,
    skill_id CHAR(36) NOT NULL,
    PRIMARY KEY (event_id, skill_id),
    FOREIGN KEY (event_id) REFERENCES EVENT (event_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES SKILL (skill_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabella: EVENT_PARTICIPATION (Gestisce la partecipazione singola o di gruppo)
CREATE TABLE EVENT_PARTICIPATION (
    participation_id CHAR(36) NOT NULL,
    event_id CHAR(36) NOT NULL,
    user_id CHAR(36) NULL, -- Partecipante individuale (per partecipazione singola)
    TEAM_id CHAR(36) NULL, -- Gruppo partecipante (per partecipazione di gruppo)
    `role` ENUM('Participant', 'Lead', 'Referee') NOT NULL,
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (participation_id),
    FOREIGN KEY (event_id) REFERENCES EVENT (event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    FOREIGN KEY (TEAM_id) REFERENCES TEAM (TEAM_id) ON DELETE CASCADE,
    -- Vincolo: una partecipazione deve essere collegata a un utente O un gruppo
    CHECK (user_id IS NOT NULL OR TEAM_id IS NOT NULL),
    -- Vincolo: evita la partecipazione singola e di gruppo simultanea
    CHECK (user_id IS NULL OR TEAM_id IS NULL),
    -- Vincolo di unicità: un utente o un gruppo può partecipare una sola volta ad un evento
    UNIQUE KEY uk_event_user (event_id, user_id),
    UNIQUE KEY uk_event_TEAM (event_id, TEAM_id)
) ENGINE=InnoDB;
CREATE INDEX idx_participation_event ON EVENT_PARTICIPATION (event_id);

-- *******************************************
-- V. Logica di Matchmaking
-- *******************************************

-- Tabella: MATCH_SCORE (per risultati pre-calcolati)
CREATE TABLE MATCH_SCORE (
    score_id CHAR(36) NOT NULL,
    user_source_id CHAR(36) NOT NULL,
    user_target_id CHAR(36) NOT NULL,
    punteggio_compatibilita DECIMAL(5, 4) NOT NULL,
    motivazione VARCHAR(255),
    data_calcolo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (score_id),
    FOREIGN KEY (user_source_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    FOREIGN KEY (user_target_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    UNIQUE KEY uk_match_pair (user_source_id, user_target_id) -- Evita duplicati nella direzione A->B
) ENGINE=InnoDB;
CREATE INDEX idx_match_target ON MATCH_SCORE (user_target_id);

-- Tabella: USER_INTERACTION (per i dati di addestramento IA)
CREATE TABLE USER_INTERACTION (
    interaction_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    tipo_azione ENUM('View Profile', 'Join Request', 'Click Suggestion', 'Create Event', 'Create TEAM') NOT NULL,
    target_id CHAR(36) NULL, -- ID dell'oggetto con cui ha interagito (utente, gruppo, evento)
    data_interazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (interaction_id),
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_interaction_user_action ON USER_INTERACTION (user_id, tipo_azione);

-- Ripristino delle impostazioni di default
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;