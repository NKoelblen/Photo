<?php
namespace App\Helpers;

class Text
{
    public static function slugify(string $text)
    {
        $text = strip_tags($text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        setlocale(LC_ALL, 'fr_FR.utf8');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}