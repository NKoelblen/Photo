<div class="row text-center row-cols-4 g-4 mb-4">
    <?php foreach ($posts as $post): ?>
        <div class="col">
            <?php require '_card.php'; ?>
        </div> <!-- .col-md-3 -->
    <?php endforeach; ?>
</div> <!-- .row -->