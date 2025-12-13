CREATE DATABASE IF NOT EXISTS unibo_matchskills_db;
USE unibo_matchskills_db;

-- I. Popolamento Anagrafiche
-- ---------------------------------

INSERT INTO SKILL (skill_id, nome, categoria) VALUES
('S001', 'Python', 'Hard'),
('S002', 'Machine Learning', 'Hard'),
('S003', 'Teamwork', 'Soft'),
('S004', 'Comunicazione', 'Soft'),
('S005', 'Cloud Computing (AWS/Azure)', 'Hard');

INSERT INTO EVENT_TYPE (type_id, nome) VALUES
('ET01', 'Hackathon Interno'),
('ET02', 'Workshop Studenti'),
('ET03', 'Gara Esterna');

INSERT INTO PARTICIPATION_TYPE (type_id, nome) VALUES
('PT01', 'On-site'),
('PT02', 'Remote');

INSERT INTO TAG (tag_id, nome) VALUES
('TAG01', 'AI'),
('TAG02', 'Coding'),
('TAG03', 'Cybersecurity'),
('TAG04', 'Networking');

-- II. Popolamento Utenti (USER)
-- ---------------------------------

-- U001: Amministratore (Admin)
INSERT INTO USER (user_id, nome, cognome, email, password_hash, is_admin, data_creazione) VALUES
('U001', 'Mario', 'Rossi', 'm.rossi@unibo.it', 'hash_admin', TRUE, NOW());

-- U002: Mentore (specializzato in ML)
INSERT INTO USER (user_id, nome, cognome, email, password_hash, is_mentor, data_creazione) VALUES
('U002', 'Chiara', 'Verdi', 'c.verdi@unibo.it', 'hash_mentor', TRUE, NOW());

-- U003: Studente A (Lead/Esperto Python)
INSERT INTO USER (user_id, nome, cognome, email, password_hash, data_creazione) VALUES
('U003', 'Luca', 'Bianchi', 'l.bianchi@unibo.it', 'hash_luca', NOW());

-- U004: Studente B (Participant/Esperto Soft Skill)
INSERT INTO USER (user_id, nome, cognome, email, password_hash, data_creazione) VALUES
('U004', 'Sofia', 'Neri', 's.neri@unibo.it', 'hash_sofia', NOW());

-- U005: Studente C (Interessato a Cloud)
INSERT INTO USER (user_id, nome, cognome, email, password_hash, data_creazione) VALUES
('U005', 'Marco', 'Gialli', 'm.gialli@unibo.it', 'hash_marco', NOW());

-- III. Popolamento Profili (USER_SKILL)
-- ---------------------------------

-- U002 (Mentore ML)
INSERT INTO USER_SKILL (user_id, skill_id, livello) VALUES
('U002', 'S002', 5), -- Machine Learning (Max level)
('U002', 'S004', 4); -- Comunicazione

-- U003 (Luca - Esperto Python)
INSERT INTO USER_SKILL (user_id, skill_id, livello) VALUES
('U003', 'S001', 5), -- Python
('U003', 'S003', 3); -- Teamwork

-- U004 (Sofia - Esperto Soft Skill)
INSERT INTO USER_SKILL (user_id, skill_id, livello) VALUES
('U004', 'S003', 5), -- Teamwork (Max level)
('U004', 'S004', 5); -- Comunicazione

-- U005 (Marco - Interessato Cloud)
INSERT INTO USER_SKILL (user_id, skill_id, livello) VALUES
('U005', 'S005', 2); -- Cloud Computing

-- IV. Popolamento Gruppi (TEAM) e Membership
-- ---------------------------------

-- T001: Team Progetto ML (Mentore U002)
INSERT INTO TEAM (TEAM_id, nome, descrizione, status, max_partecipanti, min_partecipanti, mentor_id) VALUES
('T001', 'ML Innovators', 'Progetto per il riconoscimento immagini, cerchiamo esperti Python.', 'In Ricerca', 4, 3, 'U002');

-- T002: Gruppo Studio Generico
INSERT INTO TEAM (TEAM_id, nome, descrizione, status, max_partecipanti, min_partecipanti) VALUES
('T002', 'Study Squad Python', 'Sessioni settimanali di studio Python.', 'Completo', 2, 2);

-- Competenze richieste dal T001 (ML Innovators)
INSERT INTO TEAM_REQUIRED_SKILL (TEAM_id, skill_id, livello_minimo_richiesto) VALUES
('T001', 'S001', 4), -- Python Livello 4
('T001', 'S002', 3); -- ML Livello 3

-- Membership T001 (Luca è Lead)
INSERT INTO TEAM_MEMBERSHIP (TEAM_id, user_id, role, stato_richiesta) VALUES
('T001', 'U003', 'Lead', 'Accettata'); -- Luca (U003) è Lead

-- Membership T002 (Marco è Lead, Sofia Participant)
INSERT INTO TEAM_MEMBERSHIP (TEAM_id, user_id, role, stato_richiesta) VALUES
('T002', 'U005', 'Lead', 'Accettata'), -- Marco (U005) è Lead
('T002', 'U004', 'Participant', 'Accettata'); -- Sofia (U004) è Participant

-- V. Popolamento Eventi (EVENT) e Partecipazione (CORRETTO)
-- ---------------------------------

-- E001: Hackathon Interno (Creato da TEAM T001)
INSERT INTO EVENT (event_id, titolo, descrizione, data_inizio, data_fine, luogo, max_partecipanti, status, type_id, participation_type_id, creator_TEAM_id) VALUES
('E001', 'Mini Hackathon - Img Rec', 'Competizione interna di 24h sul riconoscimento immagini.', '2026-03-10 09:00:00', NULL, 'Aula Info 3', 30, 'Attivo', 'ET01', 'PT01', 'T001');

INSERT INTO EVENT_REQUIRED_SKILL (event_id, skill_id) VALUES
('E001', 'S001'), -- Python
('E001', 'S002'); -- ML
INSERT INTO EVENT_TAG (event_id, tag_id) VALUES
('E001', 'TAG01'), -- AI
('E001', 'TAG02'); -- Coding

-- E002: Workshop (Creato da Utente U002)
INSERT INTO EVENT (event_id, titolo, descrizione, data_inizio, data_fine, luogo, max_partecipanti, status, type_id, participation_type_id, creator_user_id) VALUES
('E002', 'Workshop: Soft Skills per il Lavoro', 'Sessione introduttiva su comunicazione efficace.', '2026-02-15 14:00:00', '2026-02-15 17:00:00', 'Online (Zoom)', 50, 'Attivo', 'ET02', 'PT02', 'U002');

INSERT INTO EVENT_REQUIRED_SKILL (event_id, skill_id) VALUES
('E002', 'S004'); -- Comunicazione
INSERT INTO EVENT_TAG (event_id, tag_id) VALUES
('E002', 'TAG04'); -- Networking


-- E003: Gara Esterna (Gestita da Admin) - CORRETTO
INSERT INTO EVENT (event_id, titolo, descrizione, data_inizio, data_fine, luogo, max_partecipanti, status, type_id, participation_type_id, creator_user_id) VALUES
('E003', 'National Cybersecurity Contest', 'Gara nazionale CTF (Capture The Flag).', '2026-04-01 10:00:00', NULL, 'Roma Fiere', NULL, 'Attivo', 'ET03', 'PT01', 'U001');

INSERT INTO EVENT_EXTERNAL_DETAIL (event_id, url_originale, requisiti, livello_difficolta, scadenza_registrazione) VALUES
('E003', 'http://www.national-ctf.it', 'Minimo 3, Max 5 per team', 'Alto', '2026-03-15');

INSERT INTO EVENT_TAG (event_id, tag_id) VALUES
('E003', 'TAG03'); -- Cybersecurity

-- Partecipazione agli Eventi

-- T002 partecipa a E001 (Hackathon)
INSERT INTO EVENT_PARTICIPATION (participation_id, event_id, TEAM_id, role) VALUES
('P001', 'E001', 'T002', 'Participant');

-- U005 partecipa singolarmente a E002 (Workshop)
INSERT INTO EVENT_PARTICIPATION (participation_id, event_id, user_id, role) VALUES
('P002', 'E002', 'U005', 'Participant');

-- U004 (Sofia) è Referee per E001
INSERT INTO EVENT_PARTICIPATION (participation_id, event_id, user_id, role) VALUES
('P003', 'E001', 'U004', 'Referee');

-- VI. Popolamento Dati Matchmaking
-- ---------------------------------

-- Match Score: U003 (Python) vs U004 (Teamwork) -> Compatibilità Media (Complementari)
INSERT INTO MATCH_SCORE (score_id, user_source_id, user_target_id, punteggio_compatibilita, motivazione) VALUES
('M001', 'U003', 'U004', 0.7500, 'Skill Complementari (Hard/Soft)');

-- Match Score: U005 (Cloud) vs U001 (Admin) -> Compatibilità Bassa (Ruoli diversi)
INSERT INTO MATCH_SCORE (score_id, user_source_id, user_target_id, punteggio_compatibilita, motivazione) VALUES
('M002', 'U005', 'U001', 0.2000, 'Assenza di Skill Match');

-- Interazione: U003 visualizza il profilo di U002 (Mentore)
INSERT INTO USER_INTERACTION (interaction_id, user_id, tipo_azione, target_id) VALUES
('I001', 'U003', 'View Profile', 'U002');

-- Interazione: U004 clicca su un Suggerimento per unirsi al T001 (che cerca Teamwork)
INSERT INTO USER_INTERACTION (interaction_id, user_id, tipo_azione, target_id) VALUES
('I002', 'U004', 'Click Suggestion', 'T001');