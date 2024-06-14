<?php
namespace App\Attachment;

use App\Entity\PhotoEntity;
use Intervention\Image\ImageManagerStatic;
use Intervention\Image\ImageManager;

class PhotoAttachment
{
    private static array $formats = ['XL' => 1920, 'L' => 1440, 'M' => 1024, 'S' => 768, 'XS' => 320];
    public static function upload(PhotoEntity $entity): ?string
    {
        /**
         * @var string[] $photo ['name', 'tmp_name']
         */
        $photo = $entity->get_image();
        if (empty($photo['tmp_name'])):
            return null;
        endif;

        if (!empty($entity->get_old_image())):
            $old_path = dirname(UPLOAD_PATH) . DIRECTORY_SEPARATOR . $entity->get_path();
            $old_images[] = $old_path;
            foreach (self::$formats as $label => $width):
                $old_images[] = "{$old_path}-$label.webp";
            endforeach;
            foreach ($old_images as $old_image):
                if (file_exists($old_image)):
                    unlink($old_image);
                endif;
            endforeach;
        endif;

        $manager = ImageManager::imagick();

        $image = $manager->read($photo['tmp_name']);
        $created_at = $image->exif('EXIF')['DateTimeOriginal'];

        foreach (self::$formats as $label => $width):
            $image = $manager->read($photo['tmp_name']);
            $image->scaleDown(width: $width);
            $image->toWebp(80);
            $image->save(UPLOAD_PATH . DIRECTORY_SEPARATOR . "{$photo['name']}-$label.webp");
        endforeach;

        move_uploaded_file($photo['tmp_name'], UPLOAD_PATH . DIRECTORY_SEPARATOR . $photo['name']);
        $entity->set_path("/uploads/{$photo['name']}")
            ->set_created_at($created_at);
        return $photo['name'];
    }

    public static function detach(PhotoEntity $entity)
    {
        if (!empty($entity->get_path())):
            $path = dirname(UPLOAD_PATH) . DIRECTORY_SEPARATOR . $entity->get_path();
            $images[] = $path;
            foreach (self::$formats as $label => $width):
                $images[] = "{$path}-$label.webp";
            endforeach;
            foreach ($images as $image):
                if (file_exists($image)):
                    unlink($image);
                endif;
            endforeach;
        endif;
    }
}