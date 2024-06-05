<?php
namespace App\Migration;

final class Auth extends AbstractMigration
{
    protected static string $query = "CREATE TABLE `nk_role` (
                                          `id` varchar(255) NOT NULL,
                                          `label` varchar(255) NOT NULL,
                                          `permissions` json NOT NULL,
                                          PRIMARY KEY (`id`)
                                      );
                                  
                                      INSERT INTO nk_role
                                      VALUES
                                      (
                                         'admin',
                                         'Administrateur',
                                         '{
                                             \"photo\": {
                                                 \"read\": true,
                                                 \"create\": true,
                                                 \"edit\": true,
                                                 \"delete\": true
                                             },
                                             \"album\": {
                                                 \"read\": true,
                                                 \"create\": true,
                                                 \"edit\": true,
                                                 \"delete\": true
                                             },
                                             \"category\": {
                                                 \"public\": {
                                                     \"read\": true,
                                                     \"create\": true,
                                                     \"edit\": true,
                                                     \"delete\": true
                                                 },
                                                 \"private\": {
                                                     \"read\": true,
                                                     \"create\": true,
                                                     \"edit\": true,
                                                     \"delete\": true
                                                 }
                                             },
                                             \"location\": {
                                                 \"read\": true,
                                                 \"create\": true,
                                                 \"edit\": true,
                                                 \"delete\": true
                                             },
                                             \"user\": {
                                                 \"self\": {
                                                     \"read\": true,
                                                     \"edit\": {
                                                         \"login\": true,
                                                         \"email\": true,
                                                         \"password\": true,
                                                         \"role\": false,
                                                         \"permissions\": false
                                                     },
                                                     \"delete\": true
                                                 },
                                                 \"other\": {
                                                     \"read\": true,
                                                     \"create\": true,
                                                     \"edit\": {
                                                         \"login\": true,
                                                         \"email\": true,
                                                         \"password\": true,
                                                         \"role\": true,
                                                         \"permissions\": true
                                                     },
                                                     \"delete\": true
                                                 }
                                             }
                                         }'
                                      ),
                                      (
                                         'subscriber',
                                         'Abonné',
                                         '{
                                             \"photo\": {
                                                 \"read\": true,
                                                 \"create\": false,
                                                 \"edit\": false,
                                                 \"delete\": false
                                             },
                                             \"album\": {
                                                 \"read\": true,
                                                 \"create\": false,
                                                 \"edit\": false,
                                                 \"delete\": false
                                             },
                                             \"category\": {
                                                 \"public\": {
                                                     \"read\": true,
                                                     \"create\": false,
                                                     \"edit\": false,
                                                     \"delete\": false
                                                 },
                                                 \"private\": {
                                                     \"read\": false,
                                                     \"create\": false,
                                                     \"edit\": false,
                                                     \"delete\": false
                                                 }
                                             },
                                             \"location\": {
                                                 \"read\": true,
                                                 \"create\": false,
                                                 \"edit\": false,
                                                 \"delete\": false
                                             },
                                             \"user\": {
                                                 \"self\": {
                                                     \"read\": true,
                                                     \"edit\": {
                                                         \"login\": true,
                                                         \"email\": true,
                                                         \"password\": true,
                                                         \"role\": false,
                                                         \"permissions\": false
                                                     },
                                                     \"delete\": true
                                                 },
                                                 \"other\": {
                                                     \"read\": false,
                                                     \"create\": false,
                                                     \"edit\": {
                                                         \"login\": false,
                                                         \"email\": false,
                                                         \"password\": false,
                                                         \"role\": false,
                                                         \"permissions\": false
                                                     },
                                                     \"delete\": false
                                                 }
                                             }
                                         }'
                                      );
                                  
                                      CREATE TABLE `nk_user` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `login` varchar(255) NOT NULL UNIQUE,
                                          `email` varchar(255) NOT NULL UNIQUE,
                                          `password` varchar(55) NOT NULL,
                                          `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'subscriber',
                                          PRIMARY KEY (`id`),
                                          CONSTRAINT `fk_u_role` FOREIGN KEY (`role`) REFERENCES `nk_role` (`id`) ON DELETE SET DEFAULT ON UPDATE RESTRICT
                                      );
                                  
                                      CREATE TABLE `nk_user_category` (
                                          `user_id` int NOT NULL,
                                          `category_id` int NOT NULL,
                                          PRIMARY KEY (`user_id`, `category_id`),
                                          CONSTRAINT `fk_uc_category` FOREIGN KEY (`category_id`) REFERENCES `nk_category` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
                                          CONSTRAINT `fk_uc_user` FOREIGN KEY (`user_id`) REFERENCES `nk_user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
                                      )";
}