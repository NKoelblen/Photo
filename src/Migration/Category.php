<?php
namespace App\Migration;

final class Category extends AbstractMigration
{
    protected static string $query = "CREATE TABLE `nk_category` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `title` varchar(255) NOT NULL,
                                          `slug` varchar(255) NOT NULL UNIQUE,
                                          `status` varchar(255) NOT NULL DEFAULT 'published',
                                          `parent_id` int DEFAULT NULL,
                                          `private` tinyint(1) NOT NULL DEFAULT '0',
                                          PRIMARY KEY (`id`),
                                          CONSTRAINT `fk_c_parent` FOREIGN KEY (`parent_id`) REFERENCES `nk_category` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
                                      )";
}