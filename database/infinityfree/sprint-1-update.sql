-- B1 English Self-Study - Sprint 1 forward-only update for InfinityFree phpMyAdmin
--
-- Preconditions:
--   1. Export the current production database and keep the export off-host.
--   2. Confirm the database contains the Sprint 0 `migrations` table.
--   3. Review this file before import. It contains no DROP, DELETE, TRUNCATE, or secrets.
--
-- This batch mirrors the four approved Laravel Sprint 1 migrations and then
-- imports the same small, original pilot as SprintOneContentSeeder.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `topics` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `area` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
    `slug` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci NULL,
    `position` smallint unsigned NOT NULL DEFAULT 0,
    `priority` tinyint unsigned NOT NULL DEFAULT 2,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `topics_slug_unique` (`slug`),
    KEY `topics_area_index` (`area`),
    KEY `topics_area_status_position_index` (`area`, `status`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vocabularies` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NOT NULL,
    `term` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
    `part_of_speech` varchar(40) COLLATE utf8mb4_unicode_ci NULL,
    `phonetic` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `definition` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `translation` text COLLATE utf8mb4_unicode_ci NULL,
    `example_sentence` text COLLATE utf8mb4_unicode_ci NULL,
    `notes` text COLLATE utf8mb4_unicode_ci NULL,
    `pronunciation_audio_path` varchar(500) COLLATE utf8mb4_unicode_ci NULL,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `vocabularies_topic_term_pos_unique` (`topic_id`, `term`, `part_of_speech`),
    KEY `vocabularies_status_topic_id_index` (`status`, `topic_id`),
    KEY `vocabularies_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `vocabularies_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vocabulary_progress` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `vocabulary_id` bigint unsigned NOT NULL,
    `state` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
    `correct_count` int unsigned NOT NULL DEFAULT 0,
    `incorrect_count` int unsigned NOT NULL DEFAULT 0,
    `last_reviewed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `vocabulary_progress_vocabulary_id_unique` (`vocabulary_id`),
    CONSTRAINT `vocabulary_progress_vocabulary_id_foreign` FOREIGN KEY (`vocabulary_id`) REFERENCES `vocabularies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `grammar_lessons` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `topic_id` bigint unsigned NOT NULL,
    `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
    `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
    `objectives` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `prerequisites` text COLLATE utf8mb4_unicode_ci NULL,
    `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `examples` json NULL,
    `common_mistakes` text COLLATE utf8mb4_unicode_ci NULL,
    `position` smallint unsigned NOT NULL DEFAULT 0,
    `source_type` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `source_reference` text COLLATE utf8mb4_unicode_ci NULL,
    `license_name` varchar(120) COLLATE utf8mb4_unicode_ci NULL,
    `license_url` text COLLATE utf8mb4_unicode_ci NULL,
    `source_notes` text COLLATE utf8mb4_unicode_ci NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `grammar_lessons_slug_unique` (`slug`),
    KEY `grammar_lessons_topic_id_status_position_index` (`topic_id`, `status`, `position`),
    KEY `grammar_lessons_source_type_status_index` (`source_type`, `status`),
    CONSTRAINT `grammar_lessons_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_000100_create_topics_table', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_31_000100_create_topics_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_000200_create_vocabularies_table', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_31_000200_create_vocabularies_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_000300_create_vocabulary_progress_table', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_31_000300_create_vocabulary_progress_table'
);

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_08_31_000400_create_grammar_lessons_table', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` WHERE `migration` = '2026_08_31_000400_create_grammar_lessons_table'
);

-- Small original Sprint 1 pilot content. Re-importing updates these exact slugs
-- and term keys instead of creating duplicate rows.

INSERT INTO `topics` (`area`, `name`, `slug`, `description`, `position`, `priority`, `status`, `created_at`, `updated_at`)
VALUES
    ('vocabulary', 'Home, daily life and time', 'home-daily-life-time', 'Original starter vocabulary for routines, schedules, and everyday responsibilities.', 10, 1, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
    ('vocabulary', 'Education and study', 'education-study', 'Original starter vocabulary for study habits, courses, and learning progress.', 20, 1, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
    ('grammar', 'Core tenses', 'core-tenses', 'Original reference lessons for common B1 tense choices.', 10, 1, 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `area` = VALUES(`area`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `position` = VALUES(`position`),
    `priority` = VALUES(`priority`),
    `status` = VALUES(`status`),
    `updated_at` = UTC_TIMESTAMP();

INSERT INTO `vocabularies` (
    `topic_id`, `term`, `part_of_speech`, `phonetic`, `definition`, `translation`,
    `example_sentence`, `notes`, `pronunciation_audio_path`, `source_type`,
    `source_reference`, `license_name`, `license_url`, `source_notes`, `status`,
    `created_at`, `updated_at`
)
SELECT `id`, 'keep track of', 'phrase', NULL, 'To continue to know what is happening with something.', 'theo dõi',
    'I use a weekly plan to keep track of my study tasks.', 'Often followed by a noun such as time, progress, or expenses.',
    NULL, 'original', NULL, NULL, NULL, 'Original Sprint 1 pilot content.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
FROM `topics` WHERE `slug` = 'home-daily-life-time'
ON DUPLICATE KEY UPDATE
    `definition` = VALUES(`definition`), `translation` = VALUES(`translation`),
    `example_sentence` = VALUES(`example_sentence`), `notes` = VALUES(`notes`),
    `source_type` = VALUES(`source_type`), `source_notes` = VALUES(`source_notes`),
    `status` = VALUES(`status`), `updated_at` = UTC_TIMESTAMP();

INSERT INTO `vocabularies` (
    `topic_id`, `term`, `part_of_speech`, `phonetic`, `definition`, `translation`,
    `example_sentence`, `notes`, `pronunciation_audio_path`, `source_type`,
    `source_reference`, `license_name`, `license_url`, `source_notes`, `status`,
    `created_at`, `updated_at`
)
SELECT `id`, 'make progress', 'collocation', NULL, 'To improve or move closer to a goal.', 'tiến bộ',
    'Regular review helps me make progress in English.', 'Use make, not do, with progress.',
    NULL, 'original', NULL, NULL, NULL, 'Original Sprint 1 pilot content.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
FROM `topics` WHERE `slug` = 'education-study'
ON DUPLICATE KEY UPDATE
    `definition` = VALUES(`definition`), `translation` = VALUES(`translation`),
    `example_sentence` = VALUES(`example_sentence`), `notes` = VALUES(`notes`),
    `source_type` = VALUES(`source_type`), `source_notes` = VALUES(`source_notes`),
    `status` = VALUES(`status`), `updated_at` = UTC_TIMESTAMP();

INSERT INTO `vocabularies` (
    `topic_id`, `term`, `part_of_speech`, `phonetic`, `definition`, `translation`,
    `example_sentence`, `notes`, `pronunciation_audio_path`, `source_type`,
    `source_reference`, `license_name`, `license_url`, `source_notes`, `status`,
    `created_at`, `updated_at`
)
SELECT `id`, 'deadline', 'noun', '/ˈdedlaɪn/', 'The latest time or date by which something must be completed.', 'hạn chót',
    'The deadline for the assignment is Friday afternoon.', NULL,
    NULL, 'original', NULL, NULL, NULL, 'Original Sprint 1 pilot content.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
FROM `topics` WHERE `slug` = 'education-study'
ON DUPLICATE KEY UPDATE
    `phonetic` = VALUES(`phonetic`), `definition` = VALUES(`definition`),
    `translation` = VALUES(`translation`), `example_sentence` = VALUES(`example_sentence`),
    `source_type` = VALUES(`source_type`), `source_notes` = VALUES(`source_notes`),
    `status` = VALUES(`status`), `updated_at` = UTC_TIMESTAMP();

INSERT INTO `grammar_lessons` (
    `topic_id`, `title`, `slug`, `objectives`, `prerequisites`, `body`, `examples`,
    `common_mistakes`, `position`, `source_type`, `source_reference`, `license_name`,
    `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT `id`, 'Present simple and present continuous', 'present-simple-and-present-continuous',
    'Choose between routines or facts and actions happening around now.',
    'Basic subject–verb agreement and the verb be.',
    CONCAT('Use the present simple for routines, repeated actions, and facts.', CHAR(10), CHAR(10),
        'Use the present continuous for actions happening now or temporary situations around the present time.'),
    JSON_ARRAY(
        JSON_OBJECT('example', 'I study vocabulary every evening.', 'explanation', 'Every evening signals a repeated routine.'),
        JSON_OBJECT('example', 'I am preparing for an exam this month.', 'explanation', 'This month describes a temporary situation around now.')
    ),
    'Avoid the continuous form with many state verbs: write “I understand,” not “I am understanding.”',
    10, 'original', NULL, NULL, NULL, 'Original Sprint 1 pilot content.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
FROM `topics` WHERE `slug` = 'core-tenses'
ON DUPLICATE KEY UPDATE
    `topic_id` = VALUES(`topic_id`), `title` = VALUES(`title`),
    `objectives` = VALUES(`objectives`), `prerequisites` = VALUES(`prerequisites`),
    `body` = VALUES(`body`), `examples` = VALUES(`examples`),
    `common_mistakes` = VALUES(`common_mistakes`), `position` = VALUES(`position`),
    `source_type` = VALUES(`source_type`), `source_notes` = VALUES(`source_notes`),
    `status` = VALUES(`status`), `updated_at` = UTC_TIMESTAMP();

-- Post-import verification (expected: 4 migration rows, 3 pilot topics,
-- 3 pilot vocabulary entries, 0 progress rows, and 1 pilot grammar lesson).
SELECT `migration`, `batch` FROM `migrations` WHERE `migration` LIKE '2026_08_31_000%_create_%' ORDER BY `migration`;
SELECT COUNT(*) AS `sprint_1_topics` FROM `topics` WHERE `slug` IN ('home-daily-life-time', 'education-study', 'core-tenses');
SELECT COUNT(*) AS `sprint_1_vocabularies` FROM `vocabularies` WHERE `term` IN ('keep track of', 'make progress', 'deadline');
SELECT COUNT(*) AS `vocabulary_progress_rows` FROM `vocabulary_progress`;
SELECT COUNT(*) AS `sprint_1_grammar_lessons` FROM `grammar_lessons` WHERE `slug` = 'present-simple-and-present-continuous';
