<?php
use Faker\Factory;
use App\DataBase\DBConnection;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/config.php';

$faker = Factory::create('fr_FR');

$pdo = DBConnection::get_pdo();

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
// $pdo->exec('TRUNCATE TABLE nk_photo');
// $pdo->exec('TRUNCATE TABLE nk_category');
// $pdo->exec('TRUNCATE TABLE nk_photo_category');
// $pdo->exec('TRUNCATE TABLE nk_album');
// $pdo->exec('TRUNCATE TABLE nk_user');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

// $photos = [];

// for ($i = 51; $i <= 800; $i++):
//     $slug = $faker->unique()->slug(rand(1, 5), false);
//     $title = ucfirst(str_replace('-', ' ', $slug));
//     $date = $faker->dateTimeThisDecade()->format('Y-m-d');
//     $album_id = rand(1, 25);
//     $pdo->exec(
//         "INSERT INTO nk_photo 
//          SET title = '$title', 
//          slug = '$slug', 
//          description = '$title', 
//          created_at = '$date',
//          path = 'https://picsum.photos/id/$i/1024/768',
//          album_id = $album_id"
//     );
// endfor;

// for ($i = 1; $i <= 750; $i++):
//     $photos[] = $i;
// endfor;

// $query = $pdo->query('SELECT id FROM nk_category');
// $categories = $query->fetchAll(PDO::FETCH_COLUMN);

// foreach ($photos as $photo):
//     $random_categories = $faker->randomElements($categories, rand(1, 2));
//     foreach ($random_categories as $category):
//         $pdo->exec("INSERT INTO nk_photo_category SET photo_id=$photo, category_id=$category");
//     endforeach;
// endforeach;

// $locations = [1, 2, 3];

// foreach ($photos as $photo):
//     $random_location = $faker->randomElement($locations);
//     $pdo->exec("UPDATE nk_photo SET location_id = $random_location WHERE id = $photo");
// endforeach;

$password = password_hash('admin', HASH);
$pdo->exec("INSERT INTO nk_user SET login='admin', email='test@onoko.dev', password = '$password'");