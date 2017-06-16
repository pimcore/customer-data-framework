<?php
/** @var \CustomerManagementFrameworkBundle\Model\CustomerInterface $customer */
$customer = $this->customer;

/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<section class="content">

    <?= $this->template($cv->getDetailviewTemplate($customer)) ?>

</section>
