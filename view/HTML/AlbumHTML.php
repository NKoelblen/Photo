<?php
namespace App\HTML;

use App\Entity\AlbumEntity;

class AlbumHTML extends CollectionHTML
{
    public function card_footer(AlbumEntity $post): string
    {
        ob_start(); ?>
        <div class="card-footer">
            <?= $post->get_date_from()->format('m/Y') . ' - ' . $post->get_date_to()->format('m/Y') ?>
        </div> <!-- .card-footer -->
        <?php return ob_get_clean();
    }
}