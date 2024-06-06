<!-- Posts -->
<ul class="nav nav-tabs">

    <li class="nav-item">
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/trash') ? '' : 'active'; ?>"
            <?= str_contains($_SERVER['REQUEST_URI'], '/trash') ? '' : 'aria-current="page"'; ?>
            href="<?= $router->get_alto_router()->generate("new_{$route['singular']}"); ?>">
            Publiés
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/trash') ? 'active' : ''; ?>"
            <?= str_contains($_SERVER['REQUEST_URI'], '/trash') ? 'aria-current="page"' : ''; ?>
            href="<?= $router->get_alto_router()->generate("{$route['plural']}_trash"); ?>">
            Corbeille
        </a>
    </li>

</ul>

<table class="table table-sm table-striped table-hover align-middle mb-4">

    <thead>
        <tr>
            <th scope="col" class="px-3"><input type="checkbox"></th>
            <th scope="col" class="text-end px-3">#</th>
            <th scope="col" class="w-100 px-3">Titre</th>
            <th scope="col" class="text-center px-3">Visibilité</th>
            <th scope="col" class="px-3" style="height: 46.4px;">
                <?php if (!str_contains($_SERVER['REQUEST_URI'], '/new') && !str_contains($_SERVER['REQUEST_URI'], '/trash')): ?>
                    <!-- New -->
                    <a href="<?= $router->get_alto_router()->generate("new_{$route['singular']}"); ?>"
                        class="btn btn-success">
                        <i class="bi bi-file-earmark-plus"></i>
                    </a>
                <?php endif; ?>
            </th>
        </tr>
    </thead>

    <tbody class="table-group-divider">
        <?php foreach ($posts as $post):
            require '_rows.php';
        endforeach; ?>
    </tbody>

    <tfoot class="table-group-divider">
        <tr>
            <th scope="col" class="px-3"></th>
            <th scope="col" class="px-3"></th>
            <th scope="col" class="px-3"></th>
            <th scope="col" class="px-3"></th>
            <th scope="col" class="px-3 text-end" style="white-space: nowrap;">
                <form id="bulk" method="POST">
                    <?php if (str_contains($_SERVER['REQUEST_URI'], '/trash')): ?>
                        <!-- Restore -->
                        <button type="submit"
                            formaction="<?= $router->get_alto_router()->generate("bulk_restore_{$route['plural']}"); ?>"
                            class="btn btn-success d-inline">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        <!-- Delete -->
                        <button type="submit"
                            formaction="<?= $router->get_alto_router()->generate("bulk_delete_{$route['plural']}"); ?>"
                            class="btn btn-danger d-inline"
                            onclick="return confirm('Voulez-vous vraiment supprimer définitivement ces publications ?')">
                            <i class="bi bi-file-earmark-x"></i>
                        </button>
                    <?php else: ?>
                        <!-- Trash -->
                        <button type="submit"
                            formaction="<?= $router->get_alto_router()->generate("bulk_trash_{$route['plural']}"); ?>"
                            class="btn btn-danger"><i class="bi bi-trash3"></i></button>
                    <?php endif; ?>
                </form>
            </th>
        </tr>
    </tfoot>

</table>

<!-- Pagination -->
<?php require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'pagination.php';