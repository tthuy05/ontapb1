-- B1 English Self-Study — Sprint 0 bootstrap schema for InfinityFree phpMyAdmin
--
-- Sprint 0 has no application/domain tables. This is only Laravel's migration
-- repository, equivalent to the repository created by `php artisan migrate`
-- against an empty database. Import it once before the hosted smoke tests.
-- Do not add Sprint 1 tables or content here.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `batch` int NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
