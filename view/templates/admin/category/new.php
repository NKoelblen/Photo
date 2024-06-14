<?php
use App\HTML\Admin\CategoryHTML;
use App\HTML\Form;

$HTML = new CategoryHTML($router, $table, $labels);

// echo $HTML->alerts($errors);
echo $HTML->head($title); ?>

<div class="row g-4 my-4">

    <!-- Index -->
    <section class="col mt-0">
        <?php
        // ob_start();
        // echo $HTML->post_columns_heads();
        // $columns_heads = ob_get_clean();
        
        // ob_start();
        // echo $HTML->post_tfoot($status);
        // $tfoot = ob_get_clean();
        
        // echo $HTML->index($posts, $pagination, $link, $columns_heads, $tfoot, $status, $status_count);
        ?>
    </section> <!-- .col -->

    <!-- Form -->
    <section class="col mt-0">
        <h2>Nouvelle catégorie</h2>
        <?php
        // $form = new Form($form_post, $errors);
        
        // ob_start();
        // echo $HTML->post_inputs($form);
        /**
         * PRIVATE INPUT
         */
        // echo $HTML->recursive_inputs($form, $list);
        // $inputs = ob_get_clean();
        
        // echo $HTML->form($form, $inputs);
        ?>
    </section> <!-- .col -->

</div> <!-- .row -->