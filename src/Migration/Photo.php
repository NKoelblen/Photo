<?php
namespace App\Migration;

final class Photo extends AbstractMigration
{
    protected static string $query = "CREATE TABLE `nk_photo` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `title` varchar(255) NOT NULL,
                                          `slug` varchar(255) NOT NULL UNIQUE,
                                          `status` varchar(255) NOT NULL DEFAULT 'published' UNIQUE,
                                          `private` json,
                                          `path` varchar(255) NOT NULL,
                                          `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
                                          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                          `album_id` int DEFAULT NULL,
                                          PRIMARY KEY (`id`),
                                          CONSTRAINT `fk_p_album` FOREIGN KEY (`album_id`) REFERENCES `nk_album` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
                                      );
                                  
                                      CREATE TABLE `nk_photo_location` (
                                          `photo_id` int NOT NULL,
                                          `location_id` int NOT NULL,
                                          PRIMARY KEY (`photo_id`, `location_id`),
                                          CONSTRAINT `fk_pl_location` FOREIGN KEY (`location_id`) REFERENCES `nk_location` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                          CONSTRAINT `fk_pl_photo` FOREIGN KEY (`photo_id`) REFERENCES `nk_photo` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
                                      );

                                      CREATE TABLE `nk_photo_category` (
                                          `photo_id` int NOT NULL,
                                          `category_id` int NOT NULL,
                                          PRIMARY KEY (`photo_id`, `category_id`),
                                          CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `nk_category` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                          CONSTRAINT `fk_pc_photo` FOREIGN KEY (`photo_id`) REFERENCES `nk_photo` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
                                      )";
}