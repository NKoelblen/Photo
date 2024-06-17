<?php
namespace App\HTML\Admin;

use App\Entity\PhotoEntity;
use App\HTML\Form;

class PhotoHTML extends PostHTML
{
    public function photo_columns_heads(): string
    {
        ob_start(); ?>

        <th scope="col" class="px-3 text-center">Aperçu</th>
        <th scope="col" class="px-3" style="min-width:calc((100% - 291.97px) * 0.1667">Titre</th>
        <th scope="col" class="px-3" style="min-width:calc((100% - 291.97px) * 0.1667">Date</th>
        <th scope="col" class="px-3" style="min-width:calc((100% - 291.97px) * 0.1667">Emplacement</th>
        <th scope="col" class="px-3" style="min-width:calc((100% - 291.97px) * 0.1667">Catégories</th>
        <th scope="col" class="px-3" style="min-width:calc((100% - 291.97px) * 0.1667">Album</th>
        <th scope="col" class="text-center px-3" style="min-width:calc((100% - 291.97px) * 0.1667">Visibilité</th>

        <?php return ob_get_clean();
    }

    /**
     * @param PhotoEntity $photo
     */
    public function columns(object $photo): string
    {
        ob_start(); ?>

        <td class="px-3 text-center">
            <img src="<?= str_starts_with($photo->get_path(), 'http') ? $photo->get_path() : $photo->get_path('XS'); ?>"
                alt="Aperçu" class="img-thumbnail" style="height: 37.6px;">
        </td>
        <td class="px-3">
            <a href="<?= $this->router->get_alto_router()->generate($this->controller, ['slug' => $photo->get_slug()]) ?>">
                <?= $photo->get_title(); ?> </a>
        </td>
        <td class="px-3" style="white-space: nowrap;"><?= $photo->get_created_at()->format('d/m/Y H:i'); ?></td>
        <td class="px-3">
            <?php $location_details = $photo->get_locations();
            $locations = [];
            if ($location_details):
                foreach ($location_details as $location):
                    ob_start(); ?>
                    <a href="<?= $this->router->get_alto_router()->generate('admin-location-edit', ['id' => $location->get_id()]) ?>">
                        <?= $location->get_title(); ?>
                    </a>
                    <?php $locations[] = ob_get_clean();
                endforeach;
            endif;
            echo implode(' > ', $locations); ?>
        </td>
        <td class="px-3">
            <?php $categories_details = $photo->get_categories();
            $categories = [];
            if ($categories_details):
                foreach ($categories_details as $category):
                    ob_start(); ?>
                    <a href="<?= $this->router->get_alto_router()->generate('admin-category-edit', ['id' => $category->get_id()]); ?>">
                        <?= $category->get_title(); ?>
                    </a>
                    <?php $categories[] = ob_get_clean();
                endforeach;
            endif;
            echo implode(' | ', $categories); ?>
        </td>
        <td class="px-3">
            <?php $album = $photo->get_album();
            if ($album): ?>
                <a href="<?= $this->router->get_alto_router()->generate('admin-album-edit', ['id' => $album->get_id()]) ?>">
                    <?= $album->get_title(); ?>
                </a>
            <?php endif; ?>
        </td>
        <td class="text-center px-3">
            <?= $photo->get_private_ids() ? '<i class="bi bi-lock-fill" style="color: #dc3545"></i>' : ''; ?></td>

        <?php return ob_get_clean();
    }

    public function photo_tfoot(string $status, array $filter_locations, array $filter_categories, array $filter_albums): string
    {
        $form = new Form(new PhotoEntity, []);
        ob_start(); ?>

        <tfoot class="table-group-divider">
            <tr>
                <th scope="col" colspan="10" class="px-3 text-end nowrap">
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
                            <!-- Bulk edit -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulk-edit">
                                <i class="bi bi-pencil"></i>
                            </button>
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
                            <!-- Bulk edit -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulk-edit">
                                <i class="bi bi-pencil"></i>
                            </button>
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
        <!-- Bulk edit Form -->
        <?php if ((!isset($_GET['index-status']) || $_GET['index-status'] !== 'trashed') && $status !== 'trashed'): ?>
            <div class="modal fade" id="bulk-edit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="exampleModalLabel">Modifier les photos</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?= $form->select('status', 'Etat', ['draft' => 'Brouillon', 'published' => 'Publié']); ?>
                            <?= $this->photo_inputs($form, $filter_locations, $filter_categories, $filter_albums, ['form="bulk"']); ?>
                        </div>
                        <div class="modal-footer">
                            <?php $query = [];
                            if (isset($_GET['page'])):
                                $query['page'] = $_GET['page'];
                            endif;
                            if (isset($_GET['index-status'])):
                                $query['index-status'] = $_GET['index-status'];
                            endif;
                            $query_string = !empty($query) ? '?' . http_build_query($query) : ''; ?>
                            <button type="submit"
                                formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-edit") . $query_string; ?>"
                                form="bulk" class="btn btn-primary">
                                <i class="bi-floppy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php return ob_get_clean();
    }

    public function photo_inputs(Form $form, array $locations, array $categories, array $albums, array $attributes = []): string
    {
        ob_start();
        echo $form->input('datetime-local', 'created_at', 'Date', $attributes); ?>
        <div class="row g-3">
            <div class="col">
                <?= $form->recursive_radio('locations_ids', 'Emplacement', $locations, $attributes); ?>
            </div>
            <div class="col">
                <?= $form->recursive_checkbox('categories', 'Catégories', $categories, $attributes); ?>
            </div>
            <div class="col">
                <?= $form->radio('album_id', 'Albums', $albums, $attributes); ?>
            </div>
        </div>

        <?php return ob_get_clean();
    }

    public function bulk_create_photo(): string
    {
        ob_start(); ?>
        <form action="<?= $this->router->get_alto_router()->generate('admin-photo-new'); ?>" method="POST"
            enctype="multipart/form-data" class="py-3">
            <div class="row align-items-end">
                <div class="col">
                    <label for="image" class="form-label">Nouvelles photos</label>
                    <input type="file" name="image[]" id="image" class="form-control" multiple>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-plus"></i></button>
                </div>
            </div>
        </form>
        <?php return ob_get_clean();
    }
}