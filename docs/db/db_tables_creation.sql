-- Database creation
DROP DATABASE IF EXISTS unibo_matchskills_db;
CREATE DATABASE IF NOT EXISTS unibo_matchskills_db;
USE unibo_matchskills_db;

-- Standard settings for better table management
SET NAMES utf8mb4;
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- *******************************************
-- I. Core Entities and Lookups
-- *******************************************

CREATE TABLE USER (
    user_id CHAR(36) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    is_professor BOOLEAN DEFAULT FALSE,
    is_mentor BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB;

CREATE TABLE SKILL (
    skill_id CHAR(36) NOT NULL,
    name VARCHAR(150) NOT NULL UNIQUE,
    category ENUM('Hard', 'Soft', 'Other') NOT NULL,
    PRIMARY KEY (skill_id)
) ENGINE=InnoDB;

CREATE TABLE EVENT_TYPE (
    type_id CHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (type_id)
) ENGINE=InnoDB;

CREATE TABLE PARTICIPATION_TYPE (
    type_id CHAR(36) NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (type_id)
) ENGINE=InnoDB;

-- *******************************************
-- II. Profiling Entities
-- *******************************************

-- Table: USER_SKILL (Many-to-Many with attributes)
CREATE TABLE USER_SKILL (
    user_id CHAR(36) NOT NULL,
    skill_id CHAR(36) NOT NULL,
    level TINYINT CHECK (level BETWEEN 1 AND 5) NOT NULL,
    PRIMARY KEY (user_id, skill_id),
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES SKILL (skill_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- *******************************************
-- III. Group Entities (TEAM)
-- *******************************************

CREATE TABLE TEAM (
    team_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Searching', 'Full', 'Inactive') NOT NULL,
    max_participants SMALLINT NOT NULL,
    min_participants SMALLINT DEFAULT 1,
    mentor_id CHAR(36) NULL, -- Optional
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id),
    FOREIGN KEY (mentor_id) REFERENCES USER (user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table: TEAM_MEMBERSHIP (manages dynamic roles)
CREATE TABLE TEAM_MEMBERSHIP (
    team_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    role ENUM('Lead', 'Participant') NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_status ENUM('Accepted', 'Pending', 'Rejected') NOT NULL,
    PRIMARY KEY (team_id, user_id),
    FOREIGN KEY (team_id) REFERENCES TEAM (team_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE TEAM_REQUIRED_SKILL (
    team_id CHAR(36) NOT NULL,
    skill_id CHAR(36) NOT NULL,
    min_required_level TINYINT CHECK (min_required_level BETWEEN 1 AND 5),
    PRIMARY KEY (team_id, skill_id),
    FOREIGN KEY (team_id) REFERENCES TEAM (team_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES SKILL (skill_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- *******************************************
-- IV. Events and Participation
-- *******************************************

CREATE TABLE EVENT (
    event_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NULL,
    image_url TEXT,
    location VARCHAR(255),
    url VARCHAR(512),
    registration_deadline DATETIME,
    min_participants INT DEFAULT 0,
    max_participants INT NULL,
    status ENUM('Active', 'Completed', 'Cancelled', 'Draft') NOT NULL,
    type_id CHAR(36) NOT NULL,
    participation_type_id CHAR(36) NOT NULL,
    creator_user_id CHAR(36) NULL, -- Individual Creator (Optional)
    creator_team_id CHAR(36) NULL, -- Group Creator (Optional)
    PRIMARY KEY (event_id),
    FOREIGN KEY (type_id) REFERENCES EVENT_TYPE (type_id) ON DELETE RESTRICT,
    FOREIGN KEY (participation_type_id) REFERENCES PARTICIPATION_TYPE (type_id) ON DELETE RESTRICT,
    FOREIGN KEY (creator_user_id) REFERENCES USER (user_id) ON DELETE SET NULL,
    FOREIGN KEY (creator_team_id) REFERENCES TEAM (team_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_event_type ON EVENT (type_id);
CREATE INDEX idx_event_start_date ON EVENT (start_date);

-- Table: EVENT_REQUIRED_SKILL (Many-to-Many)
CREATE TABLE EVENT_REQUIRED_SKILL (
    event_id CHAR(36) NOT NULL,
    skill_id CHAR(36) NOT NULL,
    PRIMARY KEY (event_id, skill_id),
    FOREIGN KEY (event_id) REFERENCES EVENT (event_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES SKILL (skill_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Table: EVENT_PARTICIPATION (Handles individual or group participation)
CREATE TABLE EVENT_PARTICIPATION (
    participation_id CHAR(36) NOT NULL,
    event_id CHAR(36) NOT NULL,
    user_id CHAR(36) NULL, -- Individual participant
    team_id CHAR(36) NULL, -- Participating group
    role ENUM('Participant', 'Lead', 'Referee') NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (participation_id),
    FOREIGN KEY (event_id) REFERENCES EVENT (event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES TEAM (team_id) ON DELETE CASCADE,
    -- Constraint: participation must be linked to either a user OR a team
    CHECK (user_id IS NOT NULL OR team_id IS NOT NULL),
    -- Constraint: prevents simultaneous individual and group participation in the same record
    CHECK (user_id IS NULL OR team_id IS NULL),
    -- Uniqueness constraint: a user or team can participate only once per event
    UNIQUE KEY uk_event_user (event_id, user_id),
    UNIQUE KEY uk_event_team (event_id, team_id)
) ENGINE=InnoDB;

CREATE INDEX idx_participation_event ON EVENT_PARTICIPATION (event_id);

-- *******************************************
-- V. Matchmaking Logic
-- *******************************************

-- Table: MATCH_SCORE (for pre-calculated results)
CREATE TABLE MATCH_SCORE (
    score_id CHAR(36) NOT NULL,
    user_source_id CHAR(36) NOT NULL,
    user_target_id CHAR(36) NOT NULL,
    compatibility_score DECIMAL(5, 4) NOT NULL,
    reason VARCHAR(255),
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (score_id),
    FOREIGN KEY (user_source_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    FOREIGN KEY (user_target_id) REFERENCES USER (user_id) ON DELETE CASCADE,
    UNIQUE KEY uk_match_pair (user_source_id, user_target_id)
) ENGINE=InnoDB;

CREATE INDEX idx_match_target ON MATCH_SCORE (user_target_id);

-- Table: USER_INTERACTION (for AI training data)
CREATE TABLE USER_INTERACTION (
    interaction_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    action_type ENUM('View Profile', 'Join Request', 'Click Suggestion', 'Create Event', 'Create Team') NOT NULL,
    target_id CHAR(36) NULL, -- ID of the interacted object (user, team, event)
    interaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (interaction_id),
    FOREIGN KEY (user_id) REFERENCES USER (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_interaction_user_action ON USER_INTERACTION (user_id, action_type);

-- Restore default settings
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;