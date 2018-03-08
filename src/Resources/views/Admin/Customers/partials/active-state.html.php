<?php
/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;

/** @var \Pimcore\Model\DataObject\Customer $customer */
$customer = $this->customer;
?>

<?php if ($customer->getActive()): ?>
    <span class="fa fa-check-circle text-success tooltip-trigger" title="<?= $cv->translate('cmf_filters_customer_active') ?>"></span>
<?php else: ?>
    <span class="fa fa-times-circle text-danger tooltip-trigger" title="<?= $cv->translate('cmf_filters_customer_inactive') ?>"></span>
<?php endif; ?>
