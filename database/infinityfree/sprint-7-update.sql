-- Sprint 7 forward-only production update.
-- Run only after a read-only audit confirms the Sprint 6 baseline:
-- 19 tables, 18 migration records, and no speaking_submissions rows/table.
-- This script creates one new table, records one migration, and adds three
-- original prompts. It contains no executable ALTER, DROP, TRUNCATE, DELETE,
-- database recreation, or reset operation. Existing Sprint 0-6 rows are not
-- updated or removed.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `speaking_submissions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `speaking_prompt_id` bigint unsigned NOT NULL,
    `attempt_id` bigint unsigned NULL,
    `exam_section_item_id` bigint unsigned NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `prompt_snapshot` json NOT NULL,
    `duration_seconds` smallint unsigned NULL,
    `self_assessment` json NULL,
    `notes` text COLLATE utf8mb4_unicode_ci NULL,
    `save_version` int unsigned NOT NULL DEFAULT 0,
    `completed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `speaking_submissions_speaking_prompt_id_status_index` (`speaking_prompt_id`, `status`),
    KEY `speaking_submissions_attempt_id_status_index` (`attempt_id`, `status`),
    KEY `speaking_submissions_exam_section_item_id_status_index` (`exam_section_item_id`, `status`),
    UNIQUE KEY `speaking_submissions_attempt_item_unique` (`attempt_id`, `exam_section_item_id`),
    CONSTRAINT `speaking_submissions_speaking_prompt_id_foreign` FOREIGN KEY (`speaking_prompt_id`) REFERENCES `speaking_prompts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `speaking_submissions_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `speaking_submissions_exam_section_item_id_foreign` FOREIGN KEY (`exam_section_item_id`) REFERENCES `exam_section_items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_08_001900_create_speaking_submissions_table', 5
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_09_08_001900_create_speaking_submissions_table'
);

INSERT INTO `speaking_prompts` (
    `topic_id`, `part_type`, `title`, `instructions`, `preparation_seconds`, `speaking_seconds`,
    `suggested_ideas`, `follow_up_questions`, `checklist`, `source_type`, `source_reference`,
    `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT NULL, 'social_interaction', 'Describe your daily study routine',
    'Talk about how you usually organize a day of English study. Include when you study, what you practise, and one part you enjoy.',
    5, 45,
    '["time of day","practice activities","a useful habit"]',
    '["What makes this routine useful?","What would you like to change?"]',
    '["I answered all parts of the prompt.","I used a clear beginning, middle, and ending.","I gave at least one specific example.","I noticed one pronunciation or fluency target."]',
    'original', NULL, NULL, NULL, 'Original synthetic Sprint 7 pilot prompt.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `speaking_prompts` WHERE `title` = 'Describe your daily study routine');

INSERT INTO `speaking_prompts` (
    `topic_id`, `part_type`, `title`, `instructions`, `preparation_seconds`, `speaking_seconds`,
    `suggested_ideas`, `follow_up_questions`, `checklist`, `source_type`, `source_reference`,
    `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT NULL, 'solution_discussion', 'Choose a useful study activity',
    'Imagine that a friend wants to improve English but has only twenty minutes each day. Recommend one study activity and explain why it would help.',
    60, 120,
    '["the activity","how to do it","benefits and limitations"]',
    '["Would this activity suit every learner?","How could the learner measure progress?"]',
    '["I made a clear recommendation.","I explained two reasons.","I used an example or comparison.","I spoke continuously and linked my ideas."]',
    'original', NULL, NULL, NULL, 'Original synthetic Sprint 7 pilot prompt.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `speaking_prompts` WHERE `title` = 'Choose a useful study activity');

INSERT INTO `speaking_prompts` (
    `topic_id`, `part_type`, `title`, `instructions`, `preparation_seconds`, `speaking_seconds`,
    `suggested_ideas`, `follow_up_questions`, `checklist`, `source_type`, `source_reference`,
    `license_name`, `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT NULL, 'topic_development', 'Talk about a helpful learning habit',
    'Describe a learning habit that has helped you or someone you know. Explain how it started, what happened over time, and whether you would recommend it.',
    60, 120,
    '["the habit","a change over time","a personal example"]',
    '["Why do some habits last longer than others?","What advice would you give a beginner?"]',
    '["I developed the topic with a sequence of ideas.","I used past and present time references accurately.","I supported my view with detail.","I chose one target improvement for a second attempt."]',
    'original', NULL, NULL, NULL, 'Original synthetic Sprint 7 pilot prompt.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `speaking_prompts` WHERE `title` = 'Talk about a helpful learning habit');

-- Post-import verification queries.
SELECT `migration`, `batch` FROM `migrations`
WHERE `migration` = '2026_09_08_001900_create_speaking_submissions_table';
SELECT COUNT(*) AS `speaking_submissions_count` FROM `speaking_submissions`;
SELECT `id`, `part_type`, `title`, `status` FROM `speaking_prompts`
WHERE `title` IN ('Describe your daily study routine', 'Choose a useful study activity', 'Talk about a helpful learning habit')
ORDER BY `id`;
SELECT COUNT(*) AS `vocabulary_progress_count` FROM `vocabulary_progress`;
