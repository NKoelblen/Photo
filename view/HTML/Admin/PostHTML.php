<?php
namespace App\HTML\Admin;

use App\Entity\PostEntity;
use App\HTML\Form;

class PostHTML extends AdminHTML
{
    public function tabs(array $status_count, string $status): string
    {
        ob_start(); ?>

        <ul class="nav nav-tabs">

            <?php if (isset($status_count['published'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['index-status']) ? '' : 'active'; ?>" <?= isset($_GET['index-status']) ? '' : 'aria-current="page"'; ?>
                        href="<?= $this->router->get_alto_router()->generate("admin-$this->controller"); ?>">
                        Publié<?= ($this->labels['gender'] === 'feminine' ? 'e' : ''); ?><?= $status_count['published'] > 1 ? 's' : ''; ?>
                        (<?= $status_count['published']; ?>)
                    </a>
                </li>
            <?php endif;

            if (isset($status_count['draft'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($_GET['index-status']) && $_GET['index-status'] === 'draft') || $status === 'draft' ? 'active' : ''; ?>"
                        <?= isset($_GET['index-status']) && $_GET['index-status'] === 'draft' ? 'aria-current="page"' : ''; ?>
                        href="<?= $this->router->get_alto_router()->generate("admin-$this->controller") . '?index-status=draft'; ?>">
                        Brouillon<?= $status_count['draft'] > 1 ? 's' : ''; ?> (<?= $status_count['draft']; ?>)
                    </a>
                </li>
            <?php endif;

            if (isset($status_count['trashed'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($_GET['index-status']) && $_GET['index-status'] === 'trashed') || $status === 'trashed' ? 'active' : ''; ?>"
                        <?= isset($_GET['index-status']) && $_GET['index-status'] === 'trashed' ? 'aria-current="page"' : ''; ?>
                        href="<?= $this->router->get_alto_router()->generate("admin-$this->controller") . '?index-status=trashed'; ?>">
                        Corbeille (<?= $status_count['trashed']; ?>)
                    </a>
                </li>
            <?php endif; ?>

        </ul>

        <?php return ob_get_clean();
    }

    public function post_inputs(Form $form): string
    {
        ob_start();

        echo $form->select('status', 'Etat', ['draft' => 'Brouillon', 'published' => 'Publié']);
        echo $form->input('text', 'title', 'Titre');

        return ob_get_clean();
    }
}