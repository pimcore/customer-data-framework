<?php
/**
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 */
?>

<div class="row">
    <div class="col-md-4">
        <?= $this->template($customerView->getFieldsFilterTemplate()) ?>
    </div>

    <div class="col-md-8">

        <?= $this->template($customerView->getSegmentsFilterTemplate()) ?>

    </div>
</div>