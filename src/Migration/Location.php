<?php
namespace App\Migration;

final class Location extends AbstractMigration
{
    protected static string $query = "CREATE TABLE `nk_location` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `title` varchar(255) NOT NULL,
                                          `slug` varchar(255) NOT NULL UNIQUE,
                                          `status` varchar(255) NOT NULL DEFAULT 'published',
                                          `parent_id` int DEFAULT NULL,
                                          `coordinates` varchar(255) NOT NULL,
                                          PRIMARY KEY (`id`),
                                          CONSTRAINT `fk_l_parent` FOREIGN KEY (`parent_id`) REFERENCES `nk_location` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
                                      )";
}