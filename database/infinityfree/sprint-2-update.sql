-- B1 English Self-Study - Sprint 2 forward-only update for InfinityFree phpMyAdmin
--
-- Preconditions: the database must be the expected Sprint 0/Sprint 1 state:
-- migrations plus topics, vocabularies, vocabulary_progress, and grammar_lessons.
-- Inspect the live table list and migration rows before running this file.
-- This batch adds only the four Sprint 2 content/question tables and their
-- migration records. It does not alter the existing Sprint 1 rows.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `passages` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NULL,
    `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `word_count` smallint unsigned NOT NULL DEFAULT 0,
    `cefr_level` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B1',
    `difficulty` tinyint unsigned NOT NULL DEFAULT 2,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `passages_status_difficulty_index` (`status`, `difficulty`),
    KEY `passages_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `passages_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `listening_contents` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NULL,
    `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `transcript` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `audio_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `audio_mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `audio_size_bytes` bigint unsigned NOT NULL,
    `duration_seconds` smallint unsigned NULL,
    `speaker_count` tinyint unsigned NULL,
    `accent_notes` varchar(255) COLLATE utf8mb4_unicode_ci NULL,
    `cefr_level` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B1',
    `difficulty` tinyint unsigned NOT NULL DEFAULT 2,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `listening_contents_status_difficulty_index` (`status`, `difficulty`),
    KEY `listening_contents_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `listening_contents_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `questions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NULL,
    `passage_id` bigint unsigned NULL,
    `listening_content_id` bigint unsigned NULL,
    `skill` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
    `prompt` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `explanation` longtext COLLATE utf8mb4_unicode_ci NULL,
    `answer_config` json NULL,
    `cefr_level` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B1',
    `difficulty` tinyint unsigned NOT NULL DEFAULT 2,
    `metadata` json NULL,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `questions_skill_type_index` (`skill`, `type`),
    KEY `questions_skill_status_difficulty_index` (`skill`, `status`, `difficulty`),
    KEY `questions_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `questions_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
    CONSTRAINT `questions_passage_id_foreign` FOREIGN KEY (`passage_id`) REFERENCES `passages` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `questions_listening_content_id_foreign` FOREIGN KEY (`listening_content_id`) REFERENCES `listening_contents` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `question_options` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `question_id` bigint unsigned NOT NULL,
    `option_key` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `is_correct` tinyint(1) NOT NULL DEFAULT 0,
    `position` smallint unsigned NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `question_options_question_id_option_key_unique` (`question_id`, `option_key`),
    UNIQUE KEY `question_options_question_id_position_unique` (`question_id`, `position`),
    CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_05_000500_create_passages_table', 2
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_05_000500_create_passages_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_05_000600_create_listening_contents_table', 2
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_05_000600_create_listening_contents_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_05_000700_create_questions_table', 2
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_05_000700_create_questions_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_05_000800_create_question_options_table', 2
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_09_05_000800_create_question_options_table'
);

-- Small original Sprint 2 pilot content. The listening item intentionally
-- remains draft until a reviewed audio binary is uploaded to this path.
INSERT INTO `topics` (`area`, `name`, `slug`, `description`, `position`, `priority`, `status`, `created_at`, `updated_at`)
VALUES ('reading', 'Reading: daily plans', 'reading-daily-plans',
    'Original short texts for finding practical details and main ideas.', 10, 1, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `area` = VALUES(`area`), `name` = VALUES(`name`), `description` = VALUES(`description`),
    `position` = VALUES(`position`), `priority` = VALUES(`priority`), `status` = VALUES(`status`),
    `updated_at` = UTC_TIMESTAMP();

INSERT INTO `topics` (`area`, `name`, `slug`, `description`, `position`, `priority`, `status`, `created_at`, `updated_at`)
VALUES ('listening', 'Listening: study routines', 'listening-study-routines',
    'Original short scripts for gist, detail, and next-action listening.', 10, 1, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `area` = VALUES(`area`), `name` = VALUES(`name`), `description` = VALUES(`description`),
    `position` = VALUES(`position`), `priority` = VALUES(`priority`), `status` = VALUES(`status`),
    `updated_at` = UTC_TIMESTAMP();

SET @reading_topic_id = (SELECT `id` FROM `topics` WHERE `slug` = 'reading-daily-plans' LIMIT 1);
SET @listening_topic_id = (SELECT `id` FROM `topics` WHERE `slug` = 'listening-study-routines' LIMIT 1);

INSERT INTO `passages` (
    `topic_id`, `title`, `body`, `word_count`, `cefr_level`, `difficulty`, `source_type`,
    `source_reference`, `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT @reading_topic_id, 'A small weekly study plan',
    'Mai keeps a simple study plan on her desk. On Monday and Wednesday evenings, she reviews vocabulary for twenty minutes. On Saturday morning, she reads one short article and writes three sentences about it. She changes the plan when work becomes busy, but she always keeps one small task for the next day.',
    53, 'B1', 1, 'original', NULL, NULL, NULL, 'Original synthetic Sprint 2 pilot passage.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `passages` WHERE `title` = 'A small weekly study plan');

SET @passage_id = (SELECT `id` FROM `passages` WHERE `title` = 'A small weekly study plan' LIMIT 1);

INSERT INTO `listening_contents` (
    `topic_id`, `title`, `transcript`, `audio_path`, `audio_mime`, `audio_size_bytes`,
    `duration_seconds`, `speaker_count`, `accent_notes`, `cefr_level`, `difficulty`, `source_type`,
    `source_reference`, `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT @listening_topic_id, 'Planning a short review session',
    'I have a busy afternoon, so I will review my new words before dinner. I only need fifteen minutes. After that, I will write down one question for tomorrow’s lesson.',
    'audio/listening/planning-review-session.mp3', 'audio/mpeg', 1, 25, 1,
    'Clear, natural B1-paced speech; original script placeholder for the reviewed pilot recording.',
    'B1', 1, 'original', NULL, NULL, NULL,
    'Original synthetic Sprint 2 pilot script; audio asset is added only after recording/licence review.',
    'draft', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `listening_contents` WHERE `title` = 'Planning a short review session');

SET @listening_id = (SELECT `id` FROM `listening_contents` WHERE `title` = 'Planning a short review session' LIMIT 1);

INSERT INTO `questions` (
    `topic_id`, `passage_id`, `listening_content_id`, `skill`, `type`, `prompt`, `explanation`,
    `answer_config`, `cefr_level`, `difficulty`, `metadata`, `source_type`, `source_reference`,
    `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT @reading_topic_id, @passage_id, NULL, 'reading', 'single_choice',
    'When does Mai read a short article?',
    'The passage says that Mai reads one short article on Saturday morning.', NULL, 'B1', 1, NULL,
    'original', NULL, NULL, NULL, 'Original synthetic Sprint 2 pilot question.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `questions` WHERE `prompt` = 'When does Mai read a short article?');

SET @reading_question_id = (SELECT `id` FROM `questions` WHERE `prompt` = 'When does Mai read a short article?' LIMIT 1);

INSERT INTO `question_options` (`question_id`, `option_key`, `content`, `is_correct`, `position`, `created_at`, `updated_at`)
SELECT @reading_question_id, 'A', 'Monday evening', 0, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `question_options` WHERE `question_id` = @reading_question_id AND `option_key` = 'A');
INSERT INTO `question_options` (`question_id`, `option_key`, `content`, `is_correct`, `position`, `created_at`, `updated_at`)
SELECT @reading_question_id, 'B', 'Saturday morning', 1, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `question_options` WHERE `question_id` = @reading_question_id AND `option_key` = 'B');
INSERT INTO `question_options` (`question_id`, `option_key`, `content`, `is_correct`, `position`, `created_at`, `updated_at`)
SELECT @reading_question_id, 'C', 'Every afternoon', 0, 2, UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `question_options` WHERE `question_id` = @reading_question_id AND `option_key` = 'C');

INSERT INTO `questions` (
    `topic_id`, `passage_id`, `listening_content_id`, `skill`, `type`, `prompt`, `explanation`,
    `answer_config`, `cefr_level`, `difficulty`, `metadata`, `source_type`, `source_reference`,
    `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT @listening_topic_id, NULL, @listening_id, 'listening', 'true_false',
    'How long will the speaker review new words?',
    'The speaker says, “I only need fifteen minutes.”', NULL, 'B1', 1, NULL,
    'original', NULL, NULL, NULL, 'Original synthetic Sprint 2 pilot question.', 'draft', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `questions` WHERE `prompt` = 'How long will the speaker review new words?');

SET @listening_question_id = (SELECT `id` FROM `questions` WHERE `prompt` = 'How long will the speaker review new words?' LIMIT 1);

INSERT INTO `question_options` (`question_id`, `option_key`, `content`, `is_correct`, `position`, `created_at`, `updated_at`)
SELECT @listening_question_id, 'true', 'Fifteen minutes', 1, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `question_options` WHERE `question_id` = @listening_question_id AND `option_key` = 'true');
INSERT INTO `question_options` (`question_id`, `option_key`, `content`, `is_correct`, `position`, `created_at`, `updated_at`)
SELECT @listening_question_id, 'false', 'One hour', 0, 1, UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `question_options` WHERE `question_id` = @listening_question_id AND `option_key` = 'false');

-- Post-import verification: four Sprint 2 migrations, two new topics,
-- one active Reading passage, one draft Listening item, and two questions.
SELECT `migration`, `batch` FROM `migrations`
WHERE `migration` IN (
    '2026_09_05_000500_create_passages_table',
    '2026_09_05_000600_create_listening_contents_table',
    '2026_09_05_000700_create_questions_table',
    '2026_09_05_000800_create_question_options_table'
)
ORDER BY `migration`;
SELECT COUNT(*) AS `sprint_2_topics` FROM `topics`
WHERE `slug` IN ('reading-daily-plans', 'listening-study-routines');
SELECT COUNT(*) AS `sprint_2_passages` FROM `passages`
WHERE `title` = 'A small weekly study plan' AND `status` = 'active';
SELECT COUNT(*) AS `sprint_2_listening_drafts` FROM `listening_contents`
WHERE `title` = 'Planning a short review session' AND `status` = 'draft';
SELECT COUNT(*) AS `sprint_2_questions` FROM `questions`
WHERE `prompt` IN ('When does Mai read a short article?', 'How long will the speaker review new words?');
SELECT COUNT(*) AS `existing_vocabulary_progress_rows` FROM `vocabulary_progress`;
