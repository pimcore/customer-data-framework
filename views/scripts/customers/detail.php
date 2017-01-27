<?php
/** @var \CustomerManagementFramework\Model\CustomerInterface $customer */
$customer = $this->customer;

/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<section class="content">

    <?= $this->template($cv->getDetailviewTemplate($customer)) ?>

</section>
