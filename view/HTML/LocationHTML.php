<?php
namespace App\HTML;

class LocationHTML extends RecursiveHTML
{
    public function map(): string
    {
        ob_start(); ?>
        <div id="map" class="rounded-1 shadow my-4"></div>
        <?php return ob_get_clean();
    }
}