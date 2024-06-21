<?php if (session_status() === PHP_SESSION_NONE):
    session_start();
endif; ?>

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
    <script src="/assets/js/querystring.js" defer></script>
    <script src="/assets/js/lightbox.js" defer></script>
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/location') || $_SERVER['REQUEST_URI'] === '/'): ?>
        <script src="/assets/libraries/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="/assets/libraries/leaflet/leaflet.css">
        <script src="/assets/libraries/leaflet/leaflet.js"></script>
        <script>
            let markers = <?= json_encode($markers) ?>;
        </script>
        <link rel="stylesheet" href="/assets/libraries/leaflet/leaflet.markercluster/dist/MarkerCluster.css">
        <link rel="stylesheet" href="/assets/libraries/leaflet/leaflet.markercluster/dist/MarkerCluster.Default.css">
        <script src="/assets/libraries/leaflet/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
        <script src="/assets/js/map.js" defer></script>
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/style.css">
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
                        <?php if (!isset($_SESSION['auth'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : ''; ?>"
                                    aria-current="page" href="<?= $router->get_alto_router()->generate('home') ?>">
                                    <i class="bi bi-house"></i>
                                </a>
                            </li>
                            <?php
                        else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-house"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="<?= $router->get_alto_router()->generate('home') ?>">
                                            Accueil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="<?= $router->get_alto_router()->generate('admin') ?>">
                                            Tableau de bord
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif;
                        $controllers = [
                            'album' => 'Albums',
                            'location' => 'Emplacements',
                            'category' => 'Catégories',
                            'photo' => 'Toutes les Photos'
                        ];
                        foreach ($controllers as $controller => $label): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $_SERVER['REQUEST_URI'] === "/$controller" ? 'active' : ''; ?>"
                                    href="<?= $router->get_alto_router()->generate($controller) ?>">
                                    <?= $label; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <ul class="navbar-nav">
                    <?php if (!isset($_SESSION['auth'])): ?>
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
                                    <a class="dropdown-item <?= $_SERVER['REQUEST_URI'] === '/profile' . $_SESSION['auth'] ? 'active' : ''; ?>"
                                        href="<?= $router->get_alto_router()->generate('profile'); ?>">
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