<tr>
    <td class="px-3"><input type="checkbox" name="bulk[]" value="<?= $post->get_id(); ?>" form="bulk">
    </td>
    <td class="text-end px-3"><?= $post->get_id(); ?></td>
    <td class="px-3">
        <a
            href="<?= $router->get_alto_router()->generate($route['singular'], ['id' => $post->get_id(), 'slug' => $post->get_slug()]) ?>">
            <?= $post->get_title(); ?>
        </a>
    </td>
    <td class="text-center px-3"><?= $post->get_private() ? 'Privée' : ''; ?></td>
    <td class="px-3 nowrap">
        <?php if (str_contains($_SERVER['REQUEST_URI'], '/trash')): ?>
            <!-- Restore -->
            <form
                action="<?= $router->get_alto_router()->generate("restore_{$route['singular']}", ['id' => $post->get_id()]); ?>"
                method="POST" class="d-inline-block">
                <button type="submit" class="btn btn-success"><i class="bi bi-arrow-counterclockwise"></i></button>
            </form>
            <!-- Delete -->
            <form
                action="<?= $router->get_alto_router()->generate("delete_{$route['singular']}", ['id' => $post->get_id()]); ?>"
                method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer définitivement cette publication ?')"
                class="d-inline-block">
                <button type="submit" class="btn btn-danger"><i class="bi bi-file-earmark-x"></i></button>
            </form>
        <?php else: ?>
            <!-- Edit -->
            <a href=" <?= $router->get_alto_router()->generate("edit_{$route['singular']}", ['id' => $post->get_id()]) ?>"
                class="btn btn-primary">
                <i class="bi bi-pencil"></i>
            </a>
            <!-- Trash -->
            <form
                action="<?= $router->get_alto_router()->generate("trash_{$route['singular']}", ['id' => $post->get_id()]); ?>"
                method="POST" class="d-inline-block">
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i></button>
            </form>
        <?php endif; ?>
    </td>
</tr>