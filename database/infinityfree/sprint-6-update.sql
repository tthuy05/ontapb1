-- Sprint 6 forward-only production update.
-- Run only after a read-only audit confirms the Sprint 5 baseline:
-- 18 tables, 17 migration records, and an empty writing_prompts pilot area.
-- This script creates one new table, records one migration, and adds two
-- original prompts. It contains no ALTER, DROP, TRUNCATE, DELETE, database
-- recreation, or reset operation and does not rewrite existing content.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `writing_submissions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `writing_prompt_id` bigint unsigned NOT NULL,
    `attempt_id` bigint unsigned NULL,
    `exam_section_item_id` bigint unsigned NULL,
    `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
    `response_text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `word_count` smallint unsigned NOT NULL DEFAULT 0,
    `self_check` json NULL,
    `prompt_snapshot` json NOT NULL,
    `save_version` int unsigned NOT NULL DEFAULT 0,
    `submitted_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `writing_submissions_writing_prompt_id_status_index` (`writing_prompt_id`, `status`),
    KEY `writing_submissions_attempt_id_status_index` (`attempt_id`, `status`),
    KEY `writing_submissions_exam_section_item_id_status_index` (`exam_section_item_id`, `status`),
    UNIQUE KEY `writing_submissions_attempt_item_unique` (`attempt_id`, `exam_section_item_id`),
    CONSTRAINT `writing_submissions_writing_prompt_id_foreign` FOREIGN KEY (`writing_prompt_id`) REFERENCES `writing_prompts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `writing_submissions_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `writing_submissions_exam_section_item_id_foreign` FOREIGN KEY (`exam_section_item_id`) REFERENCES `exam_section_items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_08_001800_create_writing_submissions_table', 4
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_09_08_001800_create_writing_submissions_table'
);

INSERT INTO `writing_prompts` (
    `topic_id`, `task_type`, `title`, `instructions`, `minimum_words`, `recommended_minutes`,
    `guidance`, `checklist`, `model_answer`, `source_type`, `source_reference`, `license_name`,
    `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT NULL, 'task_1', 'Write to a study-group organizer',
    'Write an email of at least 120 words to a study-group organizer. Ask about the next meeting, explain one topic you want to practise, and suggest a useful activity.',
    120, 20,
    'Plan the purpose, audience, three required points, and a polite closing before drafting.',
    '["I answered every part of the task.","My tone suits an email to a group organizer.","My ideas follow a clear order.","I checked useful vocabulary and grammar.","I checked spelling, punctuation, and word count."]',
    NULL, 'original', NULL, NULL, NULL, 'Original synthetic Sprint 6 pilot prompt.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `writing_prompts` WHERE `title` = 'Write to a study-group organizer');

INSERT INTO `writing_prompts` (
    `topic_id`, `task_type`, `title`, `instructions`, `minimum_words`, `recommended_minutes`,
    `guidance`, `checklist`, `model_answer`, `source_type`, `source_reference`, `license_name`,
    `license_url`, `source_notes`, `status`, `created_at`, `updated_at`
)
SELECT NULL, 'task_2', 'Balancing study and free time',
    'Write an essay of at least 250 words about this question: Is it better for students to plan their free time carefully or to be spontaneous? Give reasons and examples.',
    250, 40,
    'Choose a position, outline two reasons, add an example, and reserve time to revise links and accuracy.',
    '["My position is clear.","Each paragraph has a focused idea.","I used linking expressions accurately.","I used varied vocabulary and grammar.","I checked mechanics and word count."]',
    NULL, 'original', NULL, NULL, NULL, 'Original synthetic Sprint 6 pilot prompt.', 'active', UTC_TIMESTAMP(), UTC_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM `writing_prompts` WHERE `title` = 'Balancing study and free time');

-- Post-import verification queries.
SELECT `migration`, `batch` FROM `migrations`
WHERE `migration` = '2026_09_08_001800_create_writing_submissions_table';
SELECT COUNT(*) AS `writing_submissions_count` FROM `writing_submissions`;
SELECT `id`, `task_type`, `title`, `status` FROM `writing_prompts`
WHERE `title` IN ('Write to a study-group organizer', 'Balancing study and free time')
ORDER BY `id`;
SELECT COUNT(*) AS `vocabulary_progress_count` FROM `vocabulary_progress`;
