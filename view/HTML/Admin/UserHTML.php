<?php
namespace App\HTML\Admin;

class UserHTML extends AdminHTML
{
    public function user_tfoot(string $status): string
    {
        ob_start(); ?>
        <tfoot class="table-group-divider">
            <tr>
                <th scope="col" colspan="5" class="px-3 text-end nowrap">
                    <form id="bulk" method="POST">
                        <!-- Delete -->
                        <?php $message = "Voulez-vous vraiment supprimer définitivement ces {$this->labels['plural']} ?"; ?>
                        <button type="submit"
                            formaction="<?= $this->router->get_alto_router()->generate("admin-$this->controller-delete"); ?>"
                            class="btn btn-danger d-inline" onclick="return confirm(<?= $message; ?>)">
                            <i class="bi bi-file-earmark-x"></i>
                        </button>
                    </form>
                </th>
            </tr>
        </tfoot>
        <?php return ob_get_clean();
    }

    public function inputs(): string
    {
        return '';
    }
}