CREATE DATABASE IF NOT EXISTS unibo_matchskills_db;
USE unibo_matchskills_db;

-- *******************************************
-- I. Lookup Data Population
-- *******************************************

INSERT INTO SKILL (skill_id, name, category) VALUES
('S001', 'Python', 'Hard'),
('S002', 'Machine Learning', 'Hard'),
('S003', 'Teamwork', 'Soft'),
('S004', 'Communication', 'Soft'),
('S005', 'Cloud Computing (AWS/Azure)', 'Hard');

INSERT INTO EVENT_TYPE (type_id, name) VALUES
('ET01', 'Internal Hackathon'),
('ET02', 'Student Workshop'),
('ET03', 'External Competition');

INSERT INTO PARTICIPATION_TYPE (type_id, name) VALUES
('PT01', 'On-site'),
('PT02', 'Remote');

-- *******************************************
-- II. User Population
-- *******************************************

-- U001: Admin
INSERT INTO USER (user_id, first_name, last_name, email, password_hash, is_admin, created_at) VALUES
('U001', 'Mario', 'Rossi', 'm.rossi@unibo.it', 'hash_admin', TRUE, NOW());

-- U002: Mentor (ML Specialist)
INSERT INTO USER (user_id, first_name, last_name, email, password_hash, is_mentor, created_at) VALUES
('U002', 'Chiara', 'Verdi', 'c.verdi@unibo.it', 'hash_mentor', TRUE, NOW());

-- U003: Student A (Lead/Python Expert)
INSERT INTO USER (user_id, first_name, last_name, email, password_hash, created_at) VALUES
('U003', 'Luca', 'Bianchi', 'l.bianchi@unibo.it', 'hash_luca', NOW());

-- U004: Student B (Participant/Soft Skills)
INSERT INTO USER (user_id, first_name, last_name, email, password_hash, created_at) VALUES
('U004', 'Sofia', 'Neri', 's.neri@unibo.it', 'hash_sofia', NOW());

-- U005: Student C (Cloud Interest)
INSERT INTO USER (user_id, first_name, last_name, email, password_hash, created_at) VALUES
('U005', 'Marco', 'Gialli', 'm.gialli@unibo.it', 'hash_marco', NOW());

-- *******************************************
-- III. Profiles (USER_SKILL)
-- *******************************************

INSERT INTO USER_SKILL (user_id, skill_id, level) VALUES
('U002', 'S002', 5), -- Machine Learning
('U002', 'S004', 4), -- Communication
('U003', 'S001', 5), -- Python
('U003', 'S003', 3), -- Teamwork
('U004', 'S003', 5), -- Teamwork
('U004', 'S004', 5), -- Communication
('U005', 'S005', 2); -- Cloud Computing

-- *******************************************
-- IV. Teams and Memberships
-- *******************************************

-- T001: ML Project Team
INSERT INTO TEAM (team_id, name, description, status, max_participants, min_participants, mentor_id) VALUES
('T001', 'ML Innovators', 'Image recognition project, looking for Python experts.', 'Searching', 4, 3, 'U002');

-- T002: Generic Study Group
INSERT INTO TEAM (team_id, name, description, status, max_participants, min_participants) VALUES
('T002', 'Study Squad Python', 'Weekly Python study sessions.', 'Full', 2, 2);

-- Required skills for T001
INSERT INTO TEAM_REQUIRED_SKILL (team_id, skill_id, min_required_level) VALUES
('T001', 'S001', 4), 
('T001', 'S002', 3);

-- Memberships
INSERT INTO TEAM_MEMBERSHIP (team_id, user_id, role, request_status) VALUES
('T001', 'U003', 'Lead', 'Accepted'),
('T002', 'U005', 'Lead', 'Accepted'),
('T002', 'U004', 'Participant', 'Accepted');

-- *******************************************
-- V. Events and Participation
-- *******************************************

-- E001: Created by TEAM T001
INSERT INTO EVENT (event_id, title, description, start_date, end_date, location, min_participants, max_participants, status, type_id, participation_type_id, creator_team_id) VALUES
('E001', 'Mini Hackathon - Img Rec', '24h internal competition on image recognition.', '2026-03-10 09:00:00', NULL, 'Computer Lab 3', 10, 30, 'Active', 'ET01', 'PT01', 'T001');

-- E002: Created by User U002
INSERT INTO EVENT (event_id, title, description, start_date, end_date, location, max_participants, status, type_id, participation_type_id, creator_user_id) VALUES
('E002', 'Workshop: Soft Skills for Work', 'Introductory session on effective communication.', '2026-02-15 14:00:00', '2026-02-15 17:00:00', 'Online (Zoom)', 50, 'Active', 'ET02', 'PT02', 'U002');

-- E003: External Competition (Merging old external details into EVENT)
INSERT INTO EVENT (event_id, title, description, start_date, end_date, location, url, registration_deadline, min_participants, max_participants, status, type_id, participation_type_id, creator_user_id) VALUES
('E003', 'National Cybersecurity Contest', 'National CTF (Capture The Flag) competition.', '2026-04-01 10:00:00', NULL, 'Rome Fiere', 'http://www.national-ctf.it', '2026-03-15 23:59:59', 3, 5, 'Active', 'ET03', 'PT01', 'U001');

-- Event Skills
INSERT INTO EVENT_REQUIRED_SKILL (event_id, skill_id) VALUES ('E001', 'S001'), ('E001', 'S002'), ('E002', 'S004');

-- Participation records
INSERT INTO EVENT_PARTICIPATION (participation_id, event_id, team_id, role) VALUES ('P001', 'E001', 'T002', 'Participant');
INSERT INTO EVENT_PARTICIPATION (participation_id, event_id, user_id, role) VALUES ('P002', 'E002', 'U005', 'Participant');
INSERT INTO EVENT_PARTICIPATION (participation_id, event_id, user_id, role) VALUES ('P003', 'E001', 'U004', 'Referee');

-- *******************************************
-- VI. Matchmaking and Interactions
-- *******************************************

INSERT INTO MATCH_SCORE (score_id, user_source_id, user_target_id, compatibility_score, reason) VALUES
('M001', 'U003', 'U004', 0.7500, 'Complementary Skills (Hard/Soft)'),
('M002', 'U005', 'U001', 0.2000, 'No Skill Match');

INSERT INTO USER_INTERACTION (interaction_id, user_id, action_type, target_id) VALUES
('I001', 'U003', 'View Profile', 'U002'),
('I002', 'U004', 'Click Suggestion', 'T001');