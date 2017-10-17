<?php
/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;

/** @var \Pimcore\Model\DataObject\Customer $customer */
$customer = $this->customer;
?>

<?php if ($customer->getActive()): ?>
    <span class="fa fa-check-circle text-success tooltip-trigger" title="<?= $cv->translate('active') ?>"></span>
<?php else: ?>
    <span class="fa fa-times-circle text-danger tooltip-trigger" title="<?= $cv->translate('inactive') ?>"></span>
<?php endif; ?>
