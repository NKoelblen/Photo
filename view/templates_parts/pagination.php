<?php if ($pagination->get_count() > $pagination->get_per_page()): ?>
    <ul class="pagination justify-content-center my-4">
        <li class="page-item <?= $pagination->get_current_page() <= 1 ? 'disabled' : '' ?>">
            <a href="<?= $pagination->previous_link($link); ?>" class="page-link">&laquo;</a>
        </li>
        <?php for ($i = 1; $i <= $pagination->get_pages(); $i++): ?>
            <li class="page-item <?= $pagination->get_current_page() == $i ? 'active' : ''; ?>">
                <a href="<?= $pagination->number_link($link, $i); ?>" class="page-link"><?= $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination->get_current_page() >= $pagination->get_pages() ? 'disabled' : '' ?>">
            <a href="<?= $pagination->next_link($link); ?>" class="page-link">&raquo;</a>
        </li>
    </ul>
<?php endif; ?>