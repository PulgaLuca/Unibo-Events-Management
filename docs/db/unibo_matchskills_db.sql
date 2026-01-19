-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 19, 2026 alle 18:39
-- Versione del server: 8.0.29
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unibo_matchskills_db`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `event`
--

CREATE TABLE `event` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `image_url` text COLLATE utf8mb4_unicode_ci,
  `location_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `min_participants` int DEFAULT '0',
  `max_participants` int DEFAULT NULL,
  `status` enum('Active','Completed','Cancelled','Draft') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participation_type_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator_user_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `event`
--

INSERT INTO `event` (`id`, `title`, `description`, `start_date`, `end_date`, `image_url`, `location_id`, `url`, `registration_deadline`, `min_participants`, `max_participants`, `status`, `type_id`, `participation_type_id`, `creator_user_id`) VALUES
('2be12144-fab7-4eed-b848-82f438cfa749', 'Web Development Study Group #1', 'Study Group for Web Development exam', '2026-01-16 13:40:00', '2026-01-31 13:40:00', '/assets/images/events/event-main.jpg', '3f95f3e2-79c9-48bd-8105-36c13270159c', '', '2026-01-05 13:40:00', 1, 6, 'Active', 'ET003', 'PT001', 3),
('384941d5-7ca0-4f46-8bdd-d1784814a68b', 'Hackathon - Image Recognition', '36h Image Recognition', '2026-01-09 12:23:00', '2026-01-23 12:23:00', '/assets/images/events/event-main.jpg', '84d3a991-bfa1-410c-b8d0-68d3961477ca', '', '2026-01-05 12:23:00', 5, 150, 'Draft', 'ET004', 'PT003', 4),
('EV001', 'Unibo Internal Hackathon 2026', '24h hackathon for university students', '2026-03-10 09:00:00', '2026-03-11 09:00:00', '/assets/images/events/bologna-hack.jpg', '2b501632-f318-11f0-8a00-5405db6b1978', '', '2026-01-17 00:46:00', 10, 50, 'Active', 'ET001', 'PT003', 3);

-- --------------------------------------------------------

--
-- Struttura della tabella `event_participation`
--

CREATE TABLE `event_participation` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `team_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('Participant','Lead','Referee') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `event_participation`
--

INSERT INTO `event_participation` (`id`, `event_id`, `user_id`, `team_id`, `role`, `registration_date`) VALUES
('0cef29c2-4c96-426e-9440-ec5885f3ddbd', '2be12144-fab7-4eed-b848-82f438cfa749', 3, NULL, 'Participant', '2026-01-17 11:40:52'),
('0e4087dc-247c-45ed-acc8-66776cf6bdab', 'EV001', 6, NULL, 'Participant', '2026-01-19 07:10:10'),
('2ae0add2-c7ec-4f4f-8fb7-903470178d51', '384941d5-7ca0-4f46-8bdd-d1784814a68b', 7, NULL, 'Participant', '2026-01-19 07:11:20'),
('375ac877-9eb6-4fe4-b2bd-163b30d523er', 'EV001', 4, NULL, 'Participant', '2026-01-14 13:53:51'),
('7edd7406-6d14-4220-b672-57e89f2aeb7f', '2be12144-fab7-4eed-b848-82f438cfa749', 7, NULL, 'Participant', '2026-01-19 07:11:17'),
('d0df40e8-e868-44e6-b049-b3732890953e', '2be12144-fab7-4eed-b848-82f438cfa749', 6, NULL, 'Participant', '2026-01-19 07:10:19'),
('eed50e67-dacf-4a03-b2df-2a2b428f78d0', 'EV001', 5, NULL, 'Participant', '2026-01-18 12:23:49'),
('EP001', 'EV001', 3, NULL, 'Lead', '2026-01-06 01:34:36');

-- --------------------------------------------------------

--
-- Struttura della tabella `event_required_skill`
--

CREATE TABLE `event_required_skill` (
  `event_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `event_required_skill`
--

INSERT INTO `event_required_skill` (`event_id`, `skill_id`) VALUES
('2be12144-fab7-4eed-b848-82f438cfa749', 1),
('EV001', 1),
('2be12144-fab7-4eed-b848-82f438cfa749', 2),
('EV001', 2),
('384941d5-7ca0-4f46-8bdd-d1784814a68b', 8),
('384941d5-7ca0-4f46-8bdd-d1784814a68b', 15),
('384941d5-7ca0-4f46-8bdd-d1784814a68b', 18),
('EV001', 18),
('384941d5-7ca0-4f46-8bdd-d1784814a68b', 93),
('2be12144-fab7-4eed-b848-82f438cfa749', 94),
('EV001', 94),
('384941d5-7ca0-4f46-8bdd-d1784814a68b', 95);

-- --------------------------------------------------------

--
-- Struttura della tabella `event_type`
--

CREATE TABLE `event_type` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `event_type`
--

INSERT INTO `event_type` (`id`, `name`) VALUES
('ET004', 'External Competition'),
('ET001', 'Internal Hackathon'),
('ET002', 'Student Workshop'),
('ET003', 'Study Group'),
('ET005', 'Tech Talk');

-- --------------------------------------------------------

--
-- Struttura della tabella `location`
--

CREATE TABLE `location` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `location`
--

INSERT INTO `location` (`id`, `country`, `city`, `description`) VALUES
('1306af06-27b1-46e0-9680-76876714c222', 'Germania', 'München', 'Luisenstraße 33, 80333'),
('2ae84666-b01c-4656-a7d7-ed79be682287', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50'),
('2b501632-f318-11f0-8a00-5405db6b1978', 'Italia', 'Cesena', 'Piazza Maurizio Bufalini, 1, 47521'),
('2cfc0f0c-2d2c-4aa1-a3bf-89f5968c0835', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50'),
('3f95f3e2-79c9-48bd-8105-36c13270159c', 'Svizzera', 'St. Gallen', 'Museumstrasse 32, 9000'),
('830d912c-3db8-4fb2-9ed2-d2ab164fd49c', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50'),
('84d3a991-bfa1-410c-b8d0-68d3961477ca', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50'),
('9a164d66-c383-4e7e-934d-efe1983432ad', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50'),
('eddb8091-f75a-4003-9bcd-f4299abc1094', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50'),
('f3408309-c9cb-4e53-87b3-a9506b0b8bdd', 'Italia', 'San Mauro Pascoli', 'Via del Mulino 50');

-- --------------------------------------------------------

--
-- Struttura della tabella `participation_type`
--

CREATE TABLE `participation_type` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `participation_type`
--

INSERT INTO `participation_type` (`id`, `name`) VALUES
('PT003', 'Hybrid'),
('PT001', 'On-site'),
('PT002', 'Remote');

-- --------------------------------------------------------

--
-- Struttura della tabella `phinx_log`
--

CREATE TABLE `phinx_log` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `phinx_log`
--

INSERT INTO `phinx_log` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20251219235531, 'CreateUsersTable', '2026-01-05 16:16:27', '2026-01-05 16:16:27', 0),
(20251221190150, 'AddRoleToUsers', '2026-01-05 16:16:27', '2026-01-05 16:16:27', 0),
(20251221200302, 'CreateSessionsTable', '2026-01-05 16:16:27', '2026-01-05 16:16:28', 0),
(20251228133219, 'CreateSkillsTable', '2026-01-05 16:16:28', '2026-01-05 16:16:28', 0),
(20251228133235, 'CreateUserSkills', '2026-01-05 16:16:28', '2026-01-05 16:16:28', 0),
(20260106001041, 'CreateEventTypeTable', '2026-01-06 00:30:49', '2026-01-06 00:30:49', 0),
(20260106002026, 'CreateParticipationTypeTable', '2026-01-06 00:30:57', '2026-01-06 00:30:57', 0),
(20260106004240, 'CreateTeamTable', '2026-01-06 00:31:03', '2026-01-06 00:31:03', 0),
(20260106004327, 'CreateEventTable', '2026-01-06 00:31:13', '2026-01-06 00:31:13', 0),
(20260106005539, 'CreateEventRequiredSkillTable', '2026-01-06 00:31:21', '2026-01-06 00:31:21', 0),
(20260106005853, 'CreateEventParticipationTable', '2026-01-06 00:31:30', '2026-01-06 00:31:30', 0),
(20260116195350, 'RemoveMentorIdFromTeam', '2026-01-16 18:56:22', '2026-01-16 18:56:22', 0),
(20260116201331, 'CreateLocationTableAndLinkToEvent', '2026-01-16 19:14:32', '2026-01-16 19:14:32', 0),
(20260119172453, 'CreateTeamMembershipTable', '2026-01-19 16:35:05', '2026-01-19 16:35:05', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `sessions`
--

CREATE TABLE `sessions` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `token_hash`, `user_agent`, `expires_at`, `created_at`, `updated_at`) VALUES
(3, 3, 'cf03c944b95e5f939b263f06f5ab4380ba74103cdcaaec7dd441c9d5c7efa435', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 13:26:31', '2026-01-06 14:26:31', '2026-01-06 14:26:31'),
(4, 3, '2dd3d5fcefce067ac874feebe7e69687098e18195699305e4ebce26f2635c48c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:33:53', '2026-01-07 18:33:53', '2026-01-07 18:33:53'),
(5, 4, 'f18d5f461364d137583a548ecdfe1029b9e7c8f98e6342670c0f61fbfca98bcb', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-15 14:51:38', '2026-01-14 15:51:38', '2026-01-14 15:51:38'),
(6, 3, '8354b19d595e005634f821fd50b6e6d10e1c7f7448fb32ae61720aa96ac067e7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-17 19:46:55', '2026-01-16 20:46:55', '2026-01-16 20:46:55');

-- --------------------------------------------------------

--
-- Struttura della tabella `skills`
--

CREATE TABLE `skills` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `skills`
--

INSERT INTO `skills` (`id`, `name`, `category`, `created_at`, `updated_at`) VALUES
(1, 'HTML', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(2, 'CSS', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(3, 'JavaScript', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(4, 'TypeScript', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(5, 'React', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(6, 'Vue.js', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(7, 'Angular', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(8, 'Svelte', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(9, 'Next.js', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(10, 'Nuxt.js', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(11, 'Tailwind CSS', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(12, 'Bootstrap', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(13, 'Sass/SCSS', 'Frontend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(14, 'PHP', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(15, 'Python', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(16, 'Java', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(17, 'Node.js', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(18, 'C#', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(19, 'Go', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(20, 'Ruby', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(21, 'Rust', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(22, 'Laravel', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(23, 'Symfony', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(24, 'Django', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(25, 'Flask', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(26, 'FastAPI', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(27, 'Spring Boot', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(28, 'Express.js', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(29, 'NestJS', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(30, '.NET Core', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(31, 'Ruby on Rails', 'Backend', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(32, 'MySQL', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(33, 'PostgreSQL', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(34, 'MongoDB', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(35, 'Redis', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(36, 'SQLite', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(37, 'Oracle', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(38, 'Microsoft SQL Server', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(39, 'MariaDB', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(40, 'Cassandra', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(41, 'DynamoDB', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(42, 'Elasticsearch', 'Database', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(43, 'Docker', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(44, 'Kubernetes', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(45, 'AWS', 'Cloud', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(46, 'Azure', 'Cloud', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(47, 'Google Cloud Platform', 'Cloud', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(48, 'Git', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(49, 'GitHub Actions', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(50, 'GitLab CI/CD', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(51, 'Jenkins', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(52, 'Terraform', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(53, 'Ansible', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(54, 'Linux', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(55, 'Nginx', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(56, 'Apache', 'DevOps', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(57, 'React Native', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(58, 'Flutter', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(59, 'Swift', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(60, 'Kotlin', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(61, 'Android', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(62, 'iOS', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(63, 'Ionic', 'Mobile', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(64, 'Machine Learning', 'AI/ML', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(65, 'Deep Learning', 'AI/ML', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(66, 'TensorFlow', 'AI/ML', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(67, 'PyTorch', 'AI/ML', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(68, 'Scikit-learn', 'AI/ML', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(69, 'Pandas', 'Data Science', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(70, 'NumPy', 'Data Science', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(71, 'Data Analysis', 'Data Science', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(72, 'Data Visualization', 'Data Science', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(73, 'Power BI', 'Data Science', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(74, 'Tableau', 'Data Science', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(75, 'Unit Testing', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(76, 'Integration Testing', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(77, 'Jest', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(78, 'PHPUnit', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(79, 'Pytest', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(80, 'Selenium', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(81, 'Cypress', 'Testing', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(82, 'GraphQL', 'API', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(83, 'REST API', 'API', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(84, 'gRPC', 'API', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(85, 'WebSockets', 'API', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(86, 'Microservices', 'Architecture', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(87, 'Domain-Driven Design', 'Architecture', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(88, 'Event-Driven Architecture', 'Architecture', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(89, 'RabbitMQ', 'Message Queue', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(90, 'Apache Kafka', 'Message Queue', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(91, 'Agile', 'Methodology', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(92, 'Scrum', 'Methodology', '2026-01-05 18:16:56', '2026-01-05 18:16:56'),
(93, 'Cybersecurity', 'General', '2026-01-18 11:51:03', '2026-01-18 11:51:03'),
(94, 'JS', 'General', '2026-01-18 13:04:45', '2026-01-18 13:04:45'),
(95, 'ScikitLearn', 'General', '2026-01-18 13:05:19', '2026-01-18 13:05:19'),
(96, 'F#', 'General', '2026-01-18 14:28:45', '2026-01-18 14:28:45');

-- --------------------------------------------------------

--
-- Struttura della tabella `team`
--

CREATE TABLE `team` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Searching','Full','Inactive') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_participants` smallint DEFAULT NULL,
  `min_participants` smallint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `team`
--

INSERT INTO `team` (`id`, `name`, `description`, `status`, `max_participants`, `min_participants`, `created_at`) VALUES
('TM001', 'Unibois', 'Team focused on competitive programming', 'Searching', 5, 3, '2026-01-06 01:34:36');

-- --------------------------------------------------------

--
-- Struttura della tabella `team_membership`
--

CREATE TABLE `team_membership` (
  `team_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `role` enum('Lead','Member') COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `team_membership`
--

INSERT INTO `team_membership` (`team_id`, `user_id`, `role`, `joined_at`) VALUES
('TM001', 3, 'Lead', '2026-01-19 17:37:18'),
('TM001', 5, 'Member', '2026-01-19 17:39:11'),
('TM001', 6, 'Member', '2026-01-19 17:37:44');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `created_at`, `updated_at`, `role`) VALUES
(3, 'luca.pulga@gmail.com', '$2y$12$BAKc98w/WCWA8iT68fyZhu57Xde2AGl98XJPE/oVvoJdp21lLVzeS', 'Luca', 'Pulga', '2026-01-06 02:44:35', '2026-01-18 22:05:10', 'admin'),
(4, 'lucapulga@gmail.com', '$2y$12$6pxu7rkDe0GjeVyW8f6gOOq/xZyXPfca/OBb21TGUOivs.QKryz42', 'Luca', 'Pulga', '2026-01-14 15:48:17', '2026-01-18 22:04:59', 'admin'),
(5, 'mario.rossi@gmail.com', '$2y$12$gx/vMoBHafcrSTV2gIpZaOwe82agH5EAvYcug6QVkjuc/Ma9vVR6G', 'Mario', 'Rossi', '2026-01-18 14:23:30', '2026-01-18 14:23:30', ''),
(6, 'vittoria.persa@gmail.com', '$2y$12$cU/fBx.eJqIKhak0BdSuTeK1wC.cxMlq/Oi506UErejuIAtKImAM2', 'Vittoria', 'Persa', '2026-01-19 09:09:15', '2026-01-19 09:09:15', ''),
(7, 'marco.casali@gmail.com', '$2y$12$WB6obMq2tiWebg9MCTYUN.RuoC3wDd2YVsl8g2q1o8qY/712WwG5q', 'Marco', 'Casali', '2026-01-19 09:10:40', '2026-01-19 09:10:40', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_skills`
--

CREATE TABLE `user_skills` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `level` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `created_at`, `updated_at`) VALUES
(1, 3, 65, 2, '2026-01-18 14:25:48', '2026-01-18 14:25:48'),
(2, 6, 83, 1, '2026-01-19 09:10:05', '2026-01-19 09:10:05'),
(3, 6, 85, 3, '2026-01-19 09:10:05', '2026-01-19 09:10:05'),
(4, 6, 87, 3, '2026-01-19 09:10:05', '2026-01-19 09:10:05'),
(5, 6, 30, 2, '2026-01-19 09:10:05', '2026-01-19 09:10:05'),
(6, 6, 15, 3, '2026-01-19 09:10:05', '2026-01-19 09:10:05'),
(7, 6, 32, 2, '2026-01-19 09:10:05', '2026-01-19 09:10:05'),
(8, 7, 88, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13'),
(9, 7, 34, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13'),
(10, 7, 32, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13'),
(11, 7, 7, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13'),
(12, 7, 94, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13'),
(13, 7, 61, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13'),
(14, 7, 60, 2, '2026-01-19 09:11:13', '2026-01-19 09:11:13');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `participation_type_id` (`participation_type_id`),
  ADD KEY `creator_user_id` (`creator_user_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indici per le tabelle `event_participation`
--
ALTER TABLE `event_participation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`user_id`),
  ADD UNIQUE KEY `event_id_2` (`event_id`,`team_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indici per le tabelle `event_required_skill`
--
ALTER TABLE `event_required_skill`
  ADD PRIMARY KEY (`event_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indici per le tabelle `event_type`
--
ALTER TABLE `event_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indici per le tabelle `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `participation_type`
--
ALTER TABLE `participation_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indici per le tabelle `phinx_log`
--
ALTER TABLE `phinx_log`
  ADD PRIMARY KEY (`version`);

--
-- Indici per le tabelle `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indici per le tabelle `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `team_membership`
--
ALTER TABLE `team_membership`
  ADD PRIMARY KEY (`team_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la tabella `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `event_type` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `event_ibfk_2` FOREIGN KEY (`participation_type_id`) REFERENCES `participation_type` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `event_ibfk_3` FOREIGN KEY (`creator_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `event_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `event_participation`
--
ALTER TABLE `event_participation`
  ADD CONSTRAINT `event_participation_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_participation_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_participation_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `event_required_skill`
--
ALTER TABLE `event_required_skill`
  ADD CONSTRAINT `event_required_skill_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_required_skill_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE RESTRICT;

--
-- Limiti per la tabella `team_membership`
--
ALTER TABLE `team_membership`
  ADD CONSTRAINT `team_membership_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_membership_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
