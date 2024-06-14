<?php
namespace App\HTML;

use App\Entity\PhotoEntity;

class PhotoHTML extends PostHTML
{
    /**
     * @param PhotoEntity[] $photos
     */
    public function photo_index(array $photos): string
    {
        ob_start(); ?>
        <div class="container text-center my-4 px-0">
            <div class="row row-cols-4 g-4">
                <?php foreach ($photos as $photo): ?>
                    <div class="col-md-3">
                        <a href="" data-bs-toggle="modal" data-bs-target="#modal" data-bs-id="<?= $photo->get_id(); ?>">
                            <img src="<?= str_starts_with($photo->get_path(), 'http') ? $photo->get_path() : $photo->get_path('S') ?>"
                                alt="<?= $photo->get_description(); ?>" class="w-100 rounded">
                        </a>
                    </div> <!-- .col-md-3 -->
                <?php endforeach; ?>
            </div> <!-- .row -->
        </div>
        <?php return ob_get_clean();
    }

    /**
     * @param PhotoEntity[] $photos
     */
    public function lightbox(array $photos): string
    {
        ob_start(); ?>
        <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="Lightbox" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-black" style="--bs-bg-opacity: .75; backdrop-filter: blur(10px);">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-4 z-2" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <div id="carousel" class="carousel slide">
                        <div class="carousel-inner">
                            <?php foreach ($photos as $photo): ?>
                                <div data-bs-id="<?= $photo->get_id(); ?>" class="carousel-item">
                                    <div class="d-block w-100 vh-100"></div>
                                    <div
                                        class="carousel-caption h-100 w-75 p-4 top-50 start-50 translate-middle d-md-flex flex-column justify-content-between">
                                        <section id="date-location" class="d-md-flex justify-content-between align-items-end g-4">
                                            <p class="text-muted pe-2">
                                                <?= $photo->get_created_at()->format('d/m/Y'); ?>
                                            </p>

                                            <h2 class="text-light px-2">
                                                <?= $photo->get_title(); ?>
                                                <?php // if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                                <a href="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $photo->get_id()]); ?>"
                                                    class="btn btn-primary">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <?php // endif; ?>
                                            </h2>

                                            <div class="ps-2" style="--bs-breadcrumb-divider: '>';">
                                                <ol class="breadcrumb">
                                                    <?php foreach ($photo->get_locations() as $location): ?>
                                                        <li class="breadcrumb-item">
                                                            <a
                                                                href="<?= $this->router->get_alto_router()->generate('location-show', ['slug' => $location->get_slug()]); ?>">
                                                                <?= $location->get_title(); ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            </div>
                                        </section> <!-- #date-location -->

                                        <img src="<?= str_starts_with($photo->get_path(), 'http') ? $photo->get_path() : $photo->get_path('L') ?>"
                                            class="h-75 img-fluid object-fit-contain" alt="<?= $photo->get_description(); ?>">

                                        <section id="albums-categories" class="d-md-flex justify-content-between">
                                            <div id="albums" class="pe-2" style="--bs-breadcrumb-divider: '|';">
                                                <ol class="breadcrumb">
                                                    <li class="breadcrumb-item">
                                                        <a
                                                            href="<?= $this->router->get_alto_router()->generate('album-show', ['slug' => $photo->get_album()->get_slug()]); ?>">
                                                            <?= $photo->get_album()->get_title(); ?>
                                                        </a>
                                                    </li>
                                                </ol>
                                            </div>
                                            <?php if ($photo->get_categories()): ?>
                                                <div id="categories" class="ps-2" style="--bs-breadcrumb-divider: '|';">
                                                    <ol class="breadcrumb">
                                                        <?php foreach ($photo->get_categories() as $category): ?>
                                                            <li class="breadcrumb-item">
                                                                <a
                                                                    href="<?= $this->router->get_alto_router()->generate('category-show', ['slug' => $category->get_slug()]); ?>">
                                                                    <?= $category->get_title(); ?>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ol>
                                                </div>
                                            <?php endif; ?>
                                        </section> <!-- #albums-categories -->
                                    </div> <!-- .carousel-caption -->
                                </div> <!-- .carouel-item -->
                            <?php endforeach; ?>
                        </div> <!-- .carousel-inner -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev"
                            style="filter: invert(1) grayscale(100);">
                            <span class="carousel-control-prev-icon text-light" aria-hidden="true"></span>
                            <span class="visually-hidden">Précédente</span>
                        </button> <!-- .carousel-control-prev -->
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next"
                            style="filter: invert(1) grayscale(100);">
                            <span class="carousel-control-next-icon text-light" aria-hidden="true"></span>
                            <span class="visually-hidden">Suivante</span>
                        </button> <!-- .carousel-control-next -->
                    </div> <!-- #carousel -->
                </div> <!-- #modal-content -->
            </div> <!-- .modal-dialog -->
        </div> <!-- #modal -->
        <?php return ob_get_clean();
    }
}