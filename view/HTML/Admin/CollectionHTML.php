<?php
namespace App\HTML\Admin;

use App\Entity\PostEntity;
use App\HTML\Form;
use App\Repository\Pagination;

class CollectionHTML extends PostHTML
{
    public function post_columns_heads(): string
    {
        ob_start(); ?>

        <th scope="col" class="w-100 px-3">Titre</th>
        <th scope="col" class="px-3 text-center" colspan="2">
            <p class="m-0">Photos</p>
            <p class="m-0 text-end nowrap">
                <i class="bi bi-unlock-fill pe-3" style="color: #198754"></i>
                <i class="bi bi-lock-fill ps-3" style="color: #dc3545"></i>
            </p>
        </th>

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
        <td class="px-3 text-end" style="min-width: 57.88px">
            <?= isset($post->get_photos_nb()['public']) && $post->get_photos_nb()['public'] ? $post->get_photos_nb()['public'] : '' ?>
        </td>
        <td class="px-3 text-end" style="min-width: 57.88px">
            <?= isset($post->get_photos_nb()['private']) && $post->get_photos_nb()['private'] ? $post->get_photos_nb()['private'] : '' ?>
        </td>
        <?php return ob_get_clean();
    }

    public function post_tfoot(string $status): string
    {
        ob_start(); ?>
        <tfoot class="table-group-divider">
            <tr>
                <th scope="col" colspan="6" class="px-3 text-end nowrap">
                    <form id="bulk" method="POST">
                        <?php if ((isset($_GET['index-status']) && $_GET['index-status'] === 'trashed') || $status === 'trashed'): ?>
                            <!-- Draft -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . '?status=draft'; ?>"
                                class="btn btn-warning d-inline">
                                <i class="bi bi-file-earmark-medical"></i>
                            </button>
                            <!-- Delete -->
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-delete"); ?>"
                                class="btn btn-danger d-inline"
                                onclick="return confirm('Voulez-vous vraiment supprimer définitivement ces publications ?')">
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
}