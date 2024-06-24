<?php
namespace App\HTML\Admin;

use App\Entity\PostEntity;
use App\HTML\AppHTML;
use App\HTML\Form;
use App\Repository\Pagination;
use App\Router\AppRouter;

class AdminHTML extends AppHTML
{
    protected array $labels = [];

    public function __construct(AppRouter $router, string $controller, array $labels)
    {
        parent::__construct($router, $controller);
        $this->labels = $labels;
    }

    public function head(string $title, ?string $show_link = null): string
    {
        ob_start(); ?>
        <h1 class="my-4">
            <?= $title; ?>
            <?php if ($show_link):
                if (session_status() === PHP_SESSION_NONE):
                    session_start();
                endif;
                // if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?= $show_link; ?>" class="btn btn-primary">
                    <i class="bi bi-eye"></i>
                </a>
            <?php // endif; 
            endif; ?>
        </h1>
        <?php return ob_get_clean();
    }

    public function index(array $posts, Pagination $pagination, string $link, string $columns_heads, string $tfoot, string $status, array $status_count = [])
    {
        ob_start();
        echo method_exists($this, 'tabs') && !empty($status_count) ? $this->tabs($status_count, $status) : ''; ?>

        <table id="index" class="table table-sm table-striped table-hover align-middle mb-4">

            <thead>
                <tr>
                    <th scope="col" class="px-3"><input type="checkbox"></th>
                    <th scope="col" class="text-end px-3">#</th>

                    <?= $columns_heads; ?>

                    <th scope="col" class="px-3">
                        <?php if ((!isset($_GET['index-status']) || $_GET['index-status'] !== 'trashed') && str_contains($_SERVER['REQUEST_URI'], 'edit')): ?>
                            <!-- New -->
                            <a href="<?= $this->router->get_alto_router()->generate("admin-$this->controller") . (!empty($_GET) ? '?' . http_build_query($_GET) : ''); ?>"
                                class="btn btn-success">
                                <i class="bi bi-file-earmark-plus"></i>
                            </a>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>

            <tbody class="table-group-divider">
                <?php foreach ($posts as $post):
                    echo $this->rows($post, $status);
                endforeach; ?>
            </tbody>

            <?= $tfoot; ?>

        </table>

        <!-- Pagination -->
        <?= $this->pagination($pagination, $link);

        return ob_get_clean();
    }
    public function rows(PostEntity $post, string $status): string
    {
        ob_start(); ?>

        <tr>
            <td class="px-3"><input type="checkbox" name="bulk[]" value="<?= $post->get_id(); ?>" form="bulk">
            </td>
            <td class="text-end px-3"><?= $post->get_id(); ?></td>

            <?= method_exists($this, 'columns') ? $this->columns($post) : ''; ?>

            <td class="px-3 nowrap text-end">
                <?php if ((isset($_GET['index-status']) && $_GET['index-status'] === 'trashed') || $status === 'trashed'): ?>
                    <!-- Draft -->
                    <form
                        action="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . '?status=draft'; ?>"
                        method="POST" class="d-inline-block">
                        <button type="submit" class="btn btn-warning"><i class="bi bi-file-earmark-medical"></i></button>
                    </form>
                    <!-- Delete -->
                    <form
                        action="<?= $this->router->get_alto_router()->generate("admin-$this->controller-delete", ['id' => $post->get_id()]); ?>"
                        method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer définitivement cette publication ?')"
                        class="d-inline-block">
                        <button type="submit" class="btn btn-danger"><i class="bi bi-file-earmark-x"></i></button>
                    </form>
                <?php elseif ((isset($_GET['index-status']) && $_GET['index-status'] === 'draft') || $status === 'draft'): ?>
                    <!-- Edit -->
                    <a href=" <?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . (!empty($_GET) ? '?' . http_build_query($_GET) : ''); ?>"
                        class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Publish -->
                    <form
                        action="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . '?status=published'; ?>"
                        method="POST" class="d-inline-block">
                        <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-check"></i></button>
                    </form>
                    <!-- Trash -->
                    <form
                        action="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . '?status=trashed'; ?>"
                        method="POST" class="d-inline-block">
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i></button>
                    </form>
                <?php else: ?>
                    <!-- Edit -->
                    <a href=" <?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . (!empty($_GET) ? '?' . http_build_query($_GET) : ''); ?>"
                        class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <!-- Draft -->
                    <form
                        action="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . '?status=draft'; ?>"
                        method="POST" class="d-inline-block">
                        <button type="submit" class="btn btn-warning"><i class="bi bi-file-earmark-medical"></i></button>
                    </form>
                    <!-- Trash -->
                    <form
                        action="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit", ['id' => $post->get_id()]) . '?status=trashed'; ?>"
                        method="POST" class="d-inline-block">
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i></button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>

        <?php return ob_get_clean();
    }

    public function new()
    {

    }
    public function edit()
    {

    }
    public function form(Form $form, string $inputs): string
    {
        ob_start(); ?>

        <!-- Form -->
        <form action="" method="POST" class="my-4 d-flex flex-column align-items-end" enctype="multipart/form-data">

            <?= $inputs; ?>

            <button type="submit"
                class="btn <?= str_contains($_SERVER['REQUEST_URI'], "$this->controller/edit") ? 'btn-primary' : 'btn-success'; ?>">
                <i
                    class="bi <?= str_contains($_SERVER['REQUEST_URI'], "$this->controller/edit") ? 'bi-floppy' : 'bi-file-earmark-plus'; ?>">
                </i>
            </button>
        </form>

        <?php return ob_get_clean();
    }

    public function alerts(array $errors = []): string
    {
        ob_start(); ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= ($this->labels['start-with-vowel'] ? "L'" : ($this->labels['gender'] === 'masculine' ? 'Le ' : 'La ')) . $this->labels['singular'] . " n'a pas pu être enregistré" . ($this->labels['gender'] === 'feminine' ? 'e' : '') . '. Merci de corriger les champs erronés.' ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['published'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['published'] === '1'): ?>
                    <?= ($this->labels['start-with-vowel'] ? "L'" : ($this->labels['gender'] === 'masculine' ? 'Le ' : 'La ')) . $this->labels['singular'] . ' a bien été publié' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . '.' ?>
                <?php elseif ($_GET['published'] === '2'): ?>
                    <?= 'Les ' . $this->labels['plural'] . ' ont bien été publié' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . 's.' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['draft'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['draft'] === '1'): ?>
                    <?= ($this->labels['start-with-vowel'] ? "L'" : ($this->labels['gender'] === 'masculine' ? 'Le ' : 'La ')) . $this->labels['singular'] . ' a bien été enregistré' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . ' comme brouillon.' ?>
                <?php elseif ($_GET['draft'] === '2'): ?>
                    <?= 'Les ' . $this->labels['plural'] . ' ont bien été enregistré' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . 's comme brouillons.' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['trashed'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['trashed'] === '1'): ?>
                    <?= ($this->labels['start-with-vowel'] ? "L'" : ($this->labels['gender'] === 'masculine' ? 'Le ' : 'La ')) . $this->labels['singular'] . ' a bien été mis' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . ' à la corbeille.' ?>
                <?php elseif ($_GET['trashed'] === '2'): ?>
                    <?= 'Les ' . $this->labels['plural'] . ' ont bien été mis' . ($this->labels['gender'] === 'feminine' ? 'es' : '') . ' à la corbeille.' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['delete'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['delete'] === '1'): ?>
                    <?= ($this->labels['start-with-vowel'] ? "L'" : ($this->labels['gender'] === 'masculine' ? 'Le ' : 'La ')) . $this->labels['singular'] . ' a bien été supprimé' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . '.' ?>
                <?php elseif ($_GET['delete'] === '2'): ?>
                    <?= 'Les ' . $this->labels['plural'] . ' ont bien été supprimé' . ($this->labels['gender'] === 'feminine' ? 'e' : '') . 's.' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php return ob_get_clean();
    }
}