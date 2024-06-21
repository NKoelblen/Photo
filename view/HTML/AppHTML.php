<?php
namespace App\HTML;

use App\Repository\Pagination;
use App\Router\AppRouter;

class AppHTML
{
    protected AppRouter $router;
    protected string $controller = '';

    public function __construct(AppRouter $router, string $controller = '')
    {
        $this->router = $router;
        $this->controller = $controller;
    }

    public function head(string $title, ?string $edit_link = null): string
    {
        ob_start(); ?>
        <h1 class="my-4">
            <?= $title; ?>
            <?php if($edit_link):
                if (session_status() === PHP_SESSION_NONE):
                    session_start();
                endif;
                // if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="<?= $edit_link; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                <?php // endif; 
            endif;?>
        </h1>
        <?php return ob_get_clean();
    }
    public function pagination(Pagination $pagination, string $link): string
    {
        if ($pagination->get_count() > $pagination->get_per_page()):
            ob_start();
            ?>
            <ul class="pagination justify-content-center my-4">
                <!-- Previous page -->
                <li class="page-item <?= $pagination->get_current_page() <= 1 ? 'disabled' : '' ?>">
                    <a href="<?= $pagination->previous_link($link); ?>" class="page-link">&laquo;</a>
                </li>

                <!-- First page -->
                <li class="page-item <?= $pagination->get_current_page() == 1 ? 'active' : ''; ?>">
                    <a href="<?= $pagination->number_link($link, 1); ?>" class="page-link">1</a>
                </li>

                <?php if ($pagination->get_pages() > 2): ?>

                    <!-- Numbered pages beetween first & last pages -->

                    <?php if ($pagination->get_pages() > 9):

                        if ($pagination->get_pages() > 11):
                            if ($pagination->get_current_page() - 4 > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php elseif ($pagination->get_current_page() - 3 > 2): ?>
                                <a href="<?= $pagination->number_link($link, 2); ?>" class="page-link">2</a>
                            <?php endif;
                        endif; ?>

                        <?php if ($pagination->get_current_page() - 4 <= 1):
                            for ($i = 2; $i <= 9; $i++): ?>
                                <li class="page-item <?= $pagination->get_current_page() == $i ? 'active' : ''; ?>">
                                    <a href="<?= $pagination->number_link($link, $i); ?>" class="page-link"><?= $i; ?></a>
                                </li>
                            <?php endfor;
                        elseif ($pagination->get_current_page() + 4 >= $pagination->get_pages()):
                            for ($i = $pagination->get_pages() - 8; $i <= $pagination->get_pages() - 1; $i++): ?>
                                <li class="page-item <?= $pagination->get_current_page() == $i ? 'active' : ''; ?>">
                                    <a href="<?= $pagination->number_link($link, $i); ?>" class="page-link"><?= $i; ?></a>
                                </li>
                            <?php endfor;
                        else:
                            for ($i = $pagination->get_current_page() - 3; $i <= $pagination->get_current_page() + 3; $i++): ?>
                                <li class="page-item <?= $pagination->get_current_page() == $i ? 'active' : ''; ?>">
                                    <a href="<?= $pagination->number_link($link, $i); ?>" class="page-link"><?= $i; ?></a>
                                </li>
                            <?php endfor;
                        endif;

                        if ($pagination->get_pages() > 11):
                            if ($pagination->get_current_page() + 4 < $pagination->get_pages() - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php elseif ($pagination->get_current_page() + 3 < $pagination->get_pages() - 1): ?>
                                <a href="<?= $pagination->number_link($link, 2); ?>" class="page-link"><?= $pagination->get_pages() - 1; ?></a>
                            <?php endif;
                        endif;

                    else:
                        for ($i = 2; $i <= $pagination->get_pages() - 1; $i++): ?>
                            <li class="page-item <?= $pagination->get_current_page() == $i ? 'active' : ''; ?>">
                                <a href="<?= $pagination->number_link($link, $i); ?>" class="page-link"><?= $i; ?></a>
                            </li>
                        <?php endfor;
                    endif; ?>

                <?php endif; ?>

                <!-- Last page -->
                <li class="page-item <?= $pagination->get_current_page() == $pagination->get_pages() ? 'active' : ''; ?>">
                    <a href="<?= $pagination->number_link($link, $pagination->get_pages()); ?>" class="page-link">
                        <?= $pagination->get_pages(); ?>
                    </a>
                </li>

                <!-- Next page -->
                <li class="page-item <?= $pagination->get_current_page() >= $pagination->get_pages() ? 'disabled' : '' ?>">
                    <a href="<?= $pagination->next_link($link); ?>" class="page-link">&raquo;</a>
                </li>
            </ul>
            <?php return ob_get_clean();
        endif;
        return '';
    }
    public function filter(array $locations = [], array $categories = [], array $albums = []): string
    {
        ob_start(); ?>
        <form action="" id="filter-form" class="py-3">
            <div class="row">
                <!-- Year -->
                <div class="col-auto">
                    <div class="mb-3 input-group">
                        <label for="year" class="input-group-text">Année</label>
                        <input type="number" name="year" id="year" value="<?= $_GET['year'] ?? ''; ?>" class="form-control"
                            style="width: 100px">
                    </div> <!-- .input-group -->
                </div> <!-- .col -->
                <!-- Month -->
                <div class="col-auto">
                    <div class="mb-3 input-group">
                        <label for="month" class="input-group-text">Mois</label>
                        <select name="month" id="month" class="form-control">
                            <option value=""></option>
                            <?php $months = [
                                1 => 'Janvier',
                                2 => 'Février',
                                3 => 'Mars',
                                4 => 'Avril',
                                5 => 'Mai',
                                6 => 'Juin',
                                7 => 'Juillet',
                                8 => 'Août',
                                9 => 'Septembre',
                                10 => 'Octobre',
                                11 => 'Novembre',
                                12 => 'Décembre'
                            ]; ?>
                            <?php foreach ($months as $key => $value): ?>
                                <option value="<?= $key; ?>" <?= isset($_GET['month']) && $_GET['month'] == $key ? 'selected' : ''; ?>>
                                    <?= $value; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div> <!-- .input-group -->
                </div> <!-- .col -->
                <?php if ($locations): ?>
                    <!-- Location -->
                    <div class="col-auto">
                        <div class="mb-3 input-group">
                            <label for="location_id" class="input-group-text">Emplacement</label>
                            <select name="location_id" id="location_id" class="form-control">
                                <option value=""></option>
                                <?php foreach ($locations as $key => $value): ?>
                                    <option value="<?= $key; ?>" <?= isset($_GET['location_id']) && $_GET['location_id'] == $key ? 'selected' : ''; ?>>
                                        <?= is_array($value) ? $value['label'] : $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div> <!-- .input-group -->
                    </div> <!-- .col -->
                <?php endif; ?>
                <?php if ($categories): ?>
                    <!-- Category -->
                    <div class="col-auto">
                        <div class="mb-3 input-group">
                            <label for="category_id" class="input-group-text">Catégorie</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value=""></option>
                                <?php foreach ($categories as $key => $value): ?>
                                    <option value="<?= $key; ?>" <?= isset($_GET['category_id']) && $_GET['category_id'] == $key ? 'selected' : ''; ?>>
                                        <?= is_array($value) ? $value['label'] : $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div> <!-- .input-group -->
                    </div> <!-- .col -->
                <?php endif; ?>
                <?php if ($albums): ?>
                    <!-- Album -->
                    <div class="col-auto">
                        <div class="mb-3 input-group">
                            <label for="album_id" class="input-group-text">Album</label>
                            <select name="album_id" id="album_id" class="form-control">
                                <option value=""></option>
                                <?php foreach ($albums as $key => $value): ?>
                                    <option value="<?= $key; ?>" <?= isset($_GET['album_id']) && $_GET['album_id'] == $key ? 'selected' : ''; ?>>
                                        <?= is_array($value) ? $value['label'] : $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div> <!-- .input-group -->
                    </div> <!-- .col -->
                <?php endif; ?>
                <!-- Submit -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div> <!-- .col -->
            </div> <!-- .row -->
        </form>
        <?php return ob_get_clean();
    }
}