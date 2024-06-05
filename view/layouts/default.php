<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?: 'Onoko Photos'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</head>

<body class="min-vh-100 d-flex flex-column">
    <header>
        <nav class="navbar navbar-expand-lg bg-dark border-bottom border-body">
            <div class="container">
                <a class="navbar-brand" href="<?= $router->get_alto_router()->generate('home') ?>">ONOKO Photos</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : ''; ?>"
                                aria-current="page" href="<?= $router->get_alto_router()->generate('home') ?>">
                                <i class="bi bi-house"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/albums' ? 'active' : ''; ?>"
                                href="<?= $router->get_alto_router()->generate('albums') ?>">
                                Albums
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/locations' ? 'active' : ''; ?>"
                                href="<?= $router->get_alto_router()->generate('locations') ?>">
                                Emplacements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/categories' ? 'active' : ''; ?>"
                                href="<?= $router->get_alto_router()->generate('categories') ?>">
                                Catégories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/photos' ? 'active' : ''; ?>"
                                href="<?= $router->get_alto_router()->generate('photos') ?>">
                                Toutes les photos
                            </a>
                        </li>
                    </ul>
                </div>
                <ul class="navbar-nav">
                    <?php if (session_status() === PHP_SESSION_NONE):
                        session_start();
                    endif;
                    if (!isset($_SESSION['auth'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $router->get_alto_router()->generate('login') ?>">
                                <i class="bi bi-person"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-person"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="<?= $router->get_alto_router()->generate('admin') ?>">
                                        Tableau de bord
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?= $_SERVER['REQUEST_URI'] === ('/admin/users/' . $_SESSION['auth']) ? 'active' : ''; ?>"
                                        href="<?= $router->get_alto_router()->generate('edit_profile', ['id' => $_SESSION['auth']]) ?>">
                                        Mon Profil
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="dropdown-item">
                                    <form action="<?= $router->get_alto_router()->generate('logout') ?>" method="POST">
                                        <button type="submit" class="btn btn-danger">Me déconnecter</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container mt-4">

        <?= $content; ?>

    </div>

    <footer class="bg-dark py-4 footer mt-auto">
        <div class="container">
            Page générée en <?= round(1000 * (microtime(true) - DEBUG_TIME)); ?> ms
        </div>
    </footer>

</body>

</html>