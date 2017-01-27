<?php
/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;

/** @var \CustomerManagementFramework\Model\CustomerInterface $customer */
$customer = $this->customer;

$userDetailUrl = null;
if ($cv->hasDetailView($customer)) {
    $userDetailUrl = $this->url([
        'module'     => 'CustomerManagementFramework',
        'controller' => 'customers',
        'action'     => 'detail',
        'id'         => $customer->getId()
    ], null, true);
}
?>

<tr>
    <td class="reference-id-column table-id-column">
        <?php if (null !== $userDetailUrl): ?>
            <a href="<?= $userDetailUrl ?>"><?= $customer->getId() ?></a>
        <?php else: ?>
            <a href="#" class="js-pimcore-link" data-pimcore-id="<?= $customer->getId() ?>"><?= $customer->getId() ?></a>
        <?php endif; ?>
    </td>
    <td class="icon-column icon-column--center">
        <?= $this->partial('customers/partials/active-state.php', [
            'customerView' => $cv,
            'customer'     => $customer,
            'language'     => $this->language
        ]); ?>
    </td>
    <td>
        <?= $this->escape($customer->getFirstname()) ?>
        <?= $this->escape($customer->getLastname()) ?>
    </td>
    <td>
        <?= $this->escape($customer->getEmail()) ?>
    </td>

    <td>
        <?= $this->escape($customer->getGender()) ?>
    </td>

    <td>
        <?php foreach ($customer->getAllSegments() as $segment): ?>
            <?= $cv->getViewFormatter()->formatValue($segment); ?>
        <?php endforeach; ?>
    </td>
</tr>
