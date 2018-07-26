<?php
/** @var \Zend\Paginator\Paginator|\CustomerManagementFrameworkBundle\Model\CustomerInterface[] $paginator */
$paginator = $this->paginator;

/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="table-id-column">#</th>
        <th class="reference-id-column">ID</th>
        <th class="icon-column icon-column--center"></th>
        <th><?= $cv->translate('cmf_filters_customer_name') ?></th>
        <th><?= $cv->translate('cmf_filters_customer_email') ?></th>
        <th><?= $cv->translate('cmf_filters_customer_gender') ?></th>
        <th><?= $cv->translate('cmf_filters_segments') ?></th>
    </tr>
    </thead>

    <tbody>

    <?php
    foreach ($paginator as $customer) {
        echo $this->template($cv->getOverviewTemplate($customer), [
            'customer' => $customer
        ]);
    }
    ?>

    </tbody>
</table>