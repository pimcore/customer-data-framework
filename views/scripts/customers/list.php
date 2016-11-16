<?php
/** @var Zend_Paginator|\Pimcore\Model\Object\Customer[] $paginator */
$paginator = $this->paginator;
?>

<section class="content">

    <?= $this->template('customers/partials/list-filter.php'); ?>

    <?php if ($paginator->getTotalItemCount() === 0): ?>

        <div class="callout callout-warning">
            <p>No results.</p>
        </div>

    <?php else: ?>

    <?php endif; ?>

</section>
