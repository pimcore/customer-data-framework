<?php
/** @var \Pimcore\Model\Object\Customer $customer */
$customer = $this->customer;
?>

<?php if ($customer->getActive()): ?>
    <span class="fa fa-check-circle text-success tooltip-trigger" title="active"></span>
<?php else: ?>
    <span class="fa fa-times-circle text-danger tooltip-trigger" title="inactive"></span>
<?php endif; ?>
