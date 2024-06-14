<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">

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
    <script src="/assets/js/admin/checkbox.js" defer></script>
    <script src="/assets/js/querystring.js" defer></script>
    <?php if (
        (
            str_contains($_SERVER['REQUEST_URI'], 'admin/location')
            || str_contains($_SERVER['REQUEST_URI'], 'admin/categorie')
        )
        && (
            !isset($_GET['status'])
            || $_GET['status'] !== 'trashed'
        )
    ): ?>
        <script src="/assets/js/admin/parent_children_selectors.js" defer></script>
    <?php endif;
    if (
        str_contains($_SERVER['REQUEST_URI'], 'admin/location')
        && (
            !isset($_GET['status'])
            || $_GET['status'] !== 'trashed'
        )
    ): ?>
        <script src="/assets/libraries/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="/assets/libraries/leaflet/leaflet.css">
        <script src="/assets/libraries/leaflet/leaflet.js"></script>
        <link rel="stylesheet" href="/assets/libraries/leaflet/leaflet-search.css">
        <script src="/assets/libraries/leaflet/leaflet-search.js"></script>
        <script src="/assets/js/admin/map.js" defer></script>
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body class="min-vh-100 d-flex flex-column">
    <header>
        <nav class="navbar navbar-expand-lg bg-dark border-bottom border-body" data-bs-theme="dark">
            <div class="container">
                <a class="navbar-brand" href="<?= $router->get_alto_router()->generate('home') ?>">ONOKO Photos</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">

                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-house"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <a class="dropdown-item" aria-current="page"
                                    href="<?= $router->get_alto_router()->generate('home') ?>">Accueil</a>
                                <li>
                                    <a class="dropdown-item"
                                        href="<?= $router->get_alto_router()->generate('admin') ?>">
                                        Tableau de bord
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php if (session_status() === PHP_SESSION_NONE):
                            session_start();
                        endif;
                        // if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'):
                        $controllers = [
                            'photo' => 'Photos',
                            'category' => 'Catégories',
                            'location' => 'Emplacements',
                            'album' => 'Albums',
                            'user' => 'Utilisateurs'
                        ];
                        foreach ($controllers as $controller => $label): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $_SERVER['REQUEST_URI'] === "/admin/$controller" ? 'active' : ''; ?>"
                                    href="<?= $router->get_alto_router()->generate("admin-$controller") ?>"><?= $label; ?></a>
                            </li>
                        <?php endforeach;
                        // endif; ?>
                    </ul>
                </div>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-person"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item
                                <!-- $_SERVER['REQUEST_URI'] === '/profile' ? 'active' : '' -->
                                " href="
                                    <?= $router->get_alto_router()->generate('profile') ?>
                                    ">
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
                </ul>
            </div>
        </nav>
    </header>

    <aside></aside>

    <div class="container mt-4">

        <?= $content; ?>

    </div>

    <footer class="py-4 footer mt-auto">
        <div class="container">
            <p>Page générée en <?= round(1000 * (microtime(true) - DEBUG_TIME)); ?> ms
            </p>
        </div>
    </footer>

</body>

</html>