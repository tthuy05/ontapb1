-- B1 English Self-Study - Sprint 3 forward-only update for InfinityFree phpMyAdmin
--
-- Preconditions: verify that the live database is the audited Sprint 0/Sprint 1/
-- Sprint 2 state before running this file. It must not contain any Sprint 3
-- tables or Sprint 3 migration rows. This file is intentionally forward-only:
-- it contains no DROP, TRUNCATE, RESET, or DELETE statements.
--
-- The writing, speaking, and exam dependency tables are created in migration
-- order but remain empty. Sprint 3 exposes only the exercise/attempt vertical.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `exercises` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NULL,
    `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `skill` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `instructions` text COLLATE utf8mb4_unicode_ci NULL,
    `difficulty` tinyint unsigned NOT NULL DEFAULT 2,
    `time_limit_seconds` int unsigned NULL,
    `metadata` json NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `exercises_skill_status_difficulty_index` (`skill`, `status`, `difficulty`),
    KEY `exercises_status_topic_id_index` (`status`, `topic_id`),
    CONSTRAINT `exercises_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exercise_questions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `exercise_id` bigint unsigned NOT NULL,
    `question_id` bigint unsigned NOT NULL,
    `position` smallint unsigned NOT NULL,
    `points` decimal(6,2) NOT NULL DEFAULT 1.00,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `exercise_questions_exercise_id_question_id_unique` (`exercise_id`, `question_id`),
    UNIQUE KEY `exercise_questions_exercise_id_position_unique` (`exercise_id`, `position`),
    CONSTRAINT `exercise_questions_exercise_id_foreign` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
    CONSTRAINT `exercise_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `writing_prompts` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NULL,
    `task_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
    `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `instructions` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `minimum_words` smallint unsigned NULL,
    `recommended_minutes` smallint unsigned NULL,
    `guidance` longtext COLLATE utf8mb4_unicode_ci NULL,
    `checklist` json NULL,
    `model_answer` longtext COLLATE utf8mb4_unicode_ci NULL,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `writing_prompts_topic_id_status_index` (`topic_id`, `status`),
    KEY `writing_prompts_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `writing_prompts_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `speaking_prompts` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NULL,
    `part_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
    `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `instructions` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `preparation_seconds` smallint unsigned NULL,
    `speaking_seconds` smallint unsigned NULL,
    `suggested_ideas` json NULL,
    `follow_up_questions` json NULL,
    `checklist` json NULL,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `speaking_prompts_topic_id_status_index` (`topic_id`, `status`),
    KEY `speaking_prompts_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `speaking_prompts_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exams` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `format_label` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci NULL,
    `instructions` text COLLATE utf8mb4_unicode_ci NULL,
    `time_limit_seconds` int unsigned NULL,
    `metadata` json NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `exams_format_label_status_index` (`format_label`, `status`),
    KEY `exams_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exam_sections` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `exam_id` bigint unsigned NOT NULL,
    `skill` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
    `instructions` text COLLATE utf8mb4_unicode_ci NULL,
    `position` smallint unsigned NOT NULL,
    `time_limit_seconds` int unsigned NULL,
    `navigation_mode` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free_within_section',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `exam_sections_exam_id_position_unique` (`exam_id`, `position`),
    KEY `exam_sections_exam_id_skill_index` (`exam_id`, `skill`),
    CONSTRAINT `exam_sections_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exam_section_items` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `exam_section_id` bigint unsigned NOT NULL,
    `question_id` bigint unsigned NULL,
    `writing_prompt_id` bigint unsigned NULL,
    `speaking_prompt_id` bigint unsigned NULL,
    `position` smallint unsigned NOT NULL,
    `points` decimal(6,2) NOT NULL DEFAULT 1.00,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `exam_section_items_exam_section_id_position_unique` (`exam_section_id`, `position`),
    KEY `exam_section_items_section_question_index` (`exam_section_id`, `question_id`),
    KEY `exam_section_items_section_writing_index` (`exam_section_id`, `writing_prompt_id`),
    KEY `exam_section_items_section_speaking_index` (`exam_section_id`, `speaking_prompt_id`),
    CONSTRAINT `exam_section_items_exam_section_id_foreign` FOREIGN KEY (`exam_section_id`) REFERENCES `exam_sections` (`id`) ON DELETE CASCADE,
    CONSTRAINT `exam_section_items_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `exam_section_items_writing_prompt_id_foreign` FOREIGN KEY (`writing_prompt_id`) REFERENCES `writing_prompts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `exam_section_items_speaking_prompt_id_foreign` FOREIGN KEY (`speaking_prompt_id`) REFERENCES `speaking_prompts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attempts` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `exercise_id` bigint unsigned NULL,
    `exam_id` bigint unsigned NULL,
    `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
    `completion_reason` varchar(20) COLLATE utf8mb4_unicode_ci NULL,
    `started_at` timestamp NOT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `submitted_at` timestamp NULL DEFAULT NULL,
    `score_awarded` decimal(8,2) NOT NULL DEFAULT 0.00,
    `max_score` decimal(8,2) NOT NULL DEFAULT 0.00,
    `percentage` decimal(5,2) NULL,
    `correct_count` int unsigned NOT NULL DEFAULT 0,
    `incorrect_count` int unsigned NOT NULL DEFAULT 0,
    `unanswered_count` int unsigned NOT NULL DEFAULT 0,
    `ungraded_count` int unsigned NOT NULL DEFAULT 0,
    `duration_seconds` int unsigned NULL,
    `configuration_snapshot` json NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `attempts_status_started_at_index` (`status`, `started_at`),
    KEY `attempts_submitted_at_index` (`submitted_at`),
    CONSTRAINT `attempts_exercise_id_foreign` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `attempts_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attempt_answers` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `attempt_id` bigint unsigned NOT NULL,
    `question_id` bigint unsigned NOT NULL,
    `section_position` smallint unsigned NULL,
    `question_position` smallint unsigned NOT NULL,
    `question_snapshot` json NOT NULL,
    `context_snapshot` json NULL,
    `response` json NULL,
    `answered_at` timestamp NULL DEFAULT NULL,
    `is_correct` tinyint(1) NULL,
    `points_awarded` decimal(6,2) NULL,
    `max_points` decimal(6,2) NOT NULL DEFAULT 1.00,
    `save_version` int unsigned NOT NULL DEFAULT 0,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attempt_answers_attempt_id_question_id_unique` (`attempt_id`, `question_id`),
    KEY `attempt_answers_attempt_id_is_correct_index` (`attempt_id`, `is_correct`),
    KEY `attempt_answers_attempt_positions_index` (`attempt_id`, `section_position`, `question_position`),
    CONSTRAINT `attempt_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `attempt_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_000900_create_exercises_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_000900_create_exercises_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001000_create_exercise_questions_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001000_create_exercise_questions_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001100_create_writing_prompts_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001100_create_writing_prompts_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001200_create_speaking_prompts_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001200_create_speaking_prompts_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001300_create_exams_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001300_create_exams_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001400_create_exam_sections_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001400_create_exam_sections_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001500_create_exam_section_items_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001500_create_exam_section_items_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001600_create_attempts_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001600_create_attempts_table');
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_06_001700_create_attempt_answers_table', 3
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_06_001700_create_attempt_answers_table');

-- Small original Sprint 3 exercise pilot. Existing Sprint 0/Sprint 1/Sprint 2
-- content is referenced, not rewritten; no vocabulary or progress row is touched.
SET @reading_topic_id = (SELECT `id` FROM `topics` WHERE `slug` = 'reading-daily-plans' LIMIT 1);
SET @reading_question_id = (SELECT `id` FROM `questions` WHERE `prompt` = 'When does Mai read a short article?' LIMIT 1);

INSERT INTO `exercises` (
    `topic_id`, `title`, `skill`, `instructions`, `difficulty`, `time_limit_seconds`, `status`, `created_at`, `updated_at`
)
SELECT @reading_topic_id, 'Weekly study details practice', 'reading',
    'Read the short passage, choose the best answer, and submit when you are ready to review your result.',
    1, 300, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE @reading_topic_id IS NOT NULL
  AND @reading_question_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM `exercises` WHERE `title` = 'Weekly study details practice');

SET @exercise_id = (SELECT `id` FROM `exercises` WHERE `title` = 'Weekly study details practice' LIMIT 1);

INSERT INTO `exercise_questions` (`exercise_id`, `question_id`, `position`, `points`, `created_at`, `updated_at`)
SELECT @exercise_id, @reading_question_id, 0, 1.00, UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE @exercise_id IS NOT NULL
  AND @reading_question_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM `exercise_questions`
      WHERE `exercise_id` = @exercise_id AND `question_id` = @reading_question_id
  );

-- Post-import verification: nine Sprint 3 migration rows, nine tables,
-- empty future dependency tables, one active pilot, and preserved progress.
SELECT `migration`, `batch` FROM `migrations`
WHERE `migration` IN (
    '2026_09_06_000900_create_exercises_table',
    '2026_09_06_001000_create_exercise_questions_table',
    '2026_09_06_001100_create_writing_prompts_table',
    '2026_09_06_001200_create_speaking_prompts_table',
    '2026_09_06_001300_create_exams_table',
    '2026_09_06_001400_create_exam_sections_table',
    '2026_09_06_001500_create_exam_section_items_table',
    '2026_09_06_001600_create_attempts_table',
    '2026_09_06_001700_create_attempt_answers_table'
)
ORDER BY `migration`;
SELECT COUNT(*) AS `sprint_3_exercises` FROM `exercises` WHERE `title` = 'Weekly study details practice';
SELECT COUNT(*) AS `sprint_3_exercise_questions` FROM `exercise_questions` WHERE `exercise_id` = @exercise_id;
SELECT COUNT(*) AS `future_writing_prompts` FROM `writing_prompts`;
SELECT COUNT(*) AS `future_speaking_prompts` FROM `speaking_prompts`;
SELECT COUNT(*) AS `future_exams` FROM `exams`;
SELECT COUNT(*) AS `existing_vocabulary_progress_rows` FROM `vocabulary_progress`;
