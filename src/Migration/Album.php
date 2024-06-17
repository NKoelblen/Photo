<?php
namespace App\Migration;

final class Album extends AbstractMigration
{
    protected static string $query = "CREATE TABLE `nk_album` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `title` varchar(255) NOT NULL,
                                          `slug` varchar(255) NOT NULL UNIQUE,
                                          `status` varchar(255) NOT NULL DEFAULT 'published',
                                          `private_ids` json,
                                          PRIMARY KEY (`id`)
                                      )";
}