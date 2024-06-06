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
// $pdo->exec('TRUNCATE TABLE nk_photo_album');
// $pdo->exec('TRUNCATE TABLE nk_user');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

// $photos = [];
// $categories = [];

// for ($i = 51; $i <= 150; $i++):
//     $slug = $faker->unique()->slug(1, false);
//     $title = ucwords($slug);

//     $pdo->exec("INSERT INTO nk_photo 
//     SET title='$title', 
//     slug='$slug', 
//     description='{$faker->sentence()}', 
//     created_at='{$faker->date()}',
//     path='https://picsum.photos/id/$i/1024/768'");

//     $photos[] = $pdo->lastInsertId();
// endfor;

// for ($i = 1; $i <= 10; $i++):
//     $items[] = null;
//     for ($j = 1; $j <= 10; $j++):
//         $items[] = $j;
//     endfor;
//     unset($items[$i]);
//     $slug = $faker->unique()->slug(1, false);
//     $title = ucwords($slug);
//     $parent = (int) $faker->randomElement($items);

//     $pdo->exec("INSERT INTO nk_category 
//     SET title='$title', slug='$slug', parent='$parent'");

//     $categories[] = $pdo->lastInsertId();
// endfor;

// for ($i = 1; $i <= 100; $i++):
//     $photos[] = $i;
// endfor;

// $categories = [1, 5, 6, 4, 3, 2];

// foreach ($photos as $photo):
//     $random_categories = $faker->randomElements($categories, rand(1, 3));
//     foreach ($random_categories as $category):
//         $pdo->exec("INSERT INTO nk_photo_category SET photo_id=$photo, category_id=$category");
//     endforeach;
// endforeach;

// $locations = [12, 13, 9, 10, 8, 16, 17, 15, 18, 6, 3, 4, 5, 20, 19];

// foreach ($photos as $photo):
//     $random_location = $faker->randomElement($locations);
//     $pdo->exec("UPDATE nk_photo SET location_id = $random_location WHERE id = $photo");
// endforeach;

for ($k = 1; $k <= 25; $k++):
    $slug = $faker->unique()->slug(1, false);
    $title = ucwords($slug);

    $pdo->exec("INSERT INTO nk_album SET title='$title', slug='$slug'");
endfor;


// foreach ($photos as $photo):
//     $random_albums = $faker->randomElements($albums, rand(1, 3));
//     foreach ($random_albums as $album):
//         $pdo->exec("INSERT INTO nk_photo_album SET photo_id=$photo, album_id=$album");
//     endforeach;
// endforeach;

// $password = password_hash('admin', HASH);
// $pdo->exec("INSERT INTO nk_user SET login='Onoko', email='hello@onoko.dev', password = '$password'");