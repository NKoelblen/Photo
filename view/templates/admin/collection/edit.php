<?php require dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'templates_parts/head.php'; ?>

<div class="row g-4 my-4">

    <!-- Index -->
    <section class="col mt-0">
        <?php require dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'templates_parts/admin/collection/index.php'; ?>
    </section> <!-- .col -->

    <!-- Form -->
    <section class="col mt-0">
        <h2>Modifier l'album</h2>
        <?php require dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'templates_parts/admin/collection/form.php'; ?>
    </section> <!-- .col -->

</div> <!-- .row -->