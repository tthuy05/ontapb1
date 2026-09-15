-- Sprint 11 forward-only production update.
-- Run only after a read-only audit confirms the Sprint 9/10 baseline:
-- 20 tables, 19 migration records, and no vocabulary_review_schedules table.
-- This script creates one new table and records one migration. It contains no
-- executable ALTER, DROP, TRUNCATE, DELETE, database recreation, reset, or
-- content/progress update. Existing Sprint 0-10 rows remain unchanged.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `vocabulary_review_schedules` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `vocabulary_progress_id` bigint unsigned NOT NULL,
    `due_at` timestamp NOT NULL,
    `interval_days` smallint unsigned NOT NULL DEFAULT 0,
    `streak` int unsigned NOT NULL DEFAULT 0,
    `lapses` int unsigned NOT NULL DEFAULT 0,
    `last_rating` varchar(8) COLLATE utf8mb4_unicode_ci NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `vocabulary_review_schedules_vocabulary_progress_id_unique` (`vocabulary_progress_id`),
    KEY `vocabulary_review_schedules_due_at_index` (`due_at`),
    CONSTRAINT `vocabulary_review_schedules_vocabulary_progress_id_foreign`
        FOREIGN KEY (`vocabulary_progress_id`) REFERENCES `vocabulary_progress` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_09_13_002000_create_vocabulary_review_schedules_table', 6
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_09_13_002000_create_vocabulary_review_schedules_table'
);

-- Post-import verification queries.
SELECT `migration`, `batch` FROM `migrations`
WHERE `migration` = '2026_09_13_002000_create_vocabulary_review_schedules_table';
SELECT COUNT(*) AS `vocabulary_review_schedules_count` FROM `vocabulary_review_schedules`;
SELECT COUNT(*) AS `vocabulary_progress_count` FROM `vocabulary_progress`;
SELECT COUNT(*) AS `production_table_count`
FROM `information_schema`.`tables`
WHERE `table_schema` = DATABASE() AND `table_type` = 'BASE TABLE';
SELECT COUNT(*) AS `migration_count` FROM `migrations`;
