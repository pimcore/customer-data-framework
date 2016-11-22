<?php
/** @var \CustomerManagementFramework\Model\CustomerInterface $customer */
$customer = $this->customer;

/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $customerView */
$customerView = $this->customerView;
?>

<section class="content">

    <?= $this->template($customerView->getDetailviewTemplate($customer)) ?>

</section>
