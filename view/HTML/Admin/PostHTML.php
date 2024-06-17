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
    public function post_columns_heads(): string
    {
        ob_start(); ?>

        <th scope="col" class="w-100 px-3">Titre</th>
        <th scope="col" class="text-center px-3">Visibilité</th>

        <?php return ob_get_clean();
    }

    /**
     * @param PostEntity $post
     */
    public function columns(object $post): string
    {
        ob_start(); ?>

        <td class="px-3">
            <a
                href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $post->get_slug()]) ?>">
                <?= $post->get_title(); ?>
            </a>
        </td>
        <td class="text-center px-3">
            <?= $post->get_private() ? '<i class="bi bi-lock-fill" style="color: #dc3545"></i>' : ''; ?></td>

        <?php return ob_get_clean();
    }

    public function post_tfoot(string $status): string
    {
        ob_start(); ?>
        <tfoot class="table-group-divider">
            <tr>
                <th scope="col" colspan="5" class="px-3 text-end nowrap">
                    <form id="bulk" method="POST">
                        <?php if ((isset($_GET['index-status']) && $_GET['index-status'] === 'trashed') || $status === 'trashed'): ?>
                            <!-- Draft -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . '?status=draft'; ?>"
                                class="btn btn-warning d-inline">
                                <i class="bi bi-file-earmark-medical"></i>
                            </button>
                            <!-- Delete -->
                            <?php $message = "Voulez-vous vraiment supprimer définitivement ces {$this->labels['plural']} ?"; ?>
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-delete"); ?>"
                                class="btn btn-danger d-inline" onclick="return confirm(<?= $message; ?>)">
                                <i class="bi bi-file-earmark-x"></i>
                            </button>
                        <?php elseif ((isset($_GET['index-status']) && $_GET['index-status'] === 'draft') || $status === 'draft'): ?>
                            <!-- Publish -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . '?status=published'; ?>"
                                class="btn btn-success d-inline">
                                <i class="bi bi-file-earmark-check"></i>
                            </button>
                            <!-- Trash -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . '?status=trashed'; ?>"
                                class="btn btn-danger"><i class="bi bi-trash3"></i></button>
                        <?php else: ?>
                            <!-- Draft -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . '?status=draft'; ?>"
                                class="btn btn-warning d-inline">
                                <i class="bi bi-file-earmark-medical"></i>
                            </button>
                            <!-- Trash -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . '?status=trashed'; ?>"
                                class="btn btn-danger"><i class="bi bi-trash3"></i></button>
                        <?php endif; ?>
                    </form>
                </th>
            </tr>
        </tfoot>
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