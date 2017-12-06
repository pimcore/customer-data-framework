<?php
/**
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 * @var array $filters
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition $filterDefinition
 */
?>

<fieldset>
    <legend>
        <?= $customerView->translate('Customer') ?>
    </legend>

    <div class="form-group">
        <label for="form-filter-id"><?= $customerView->translate('Customer ID') ?></label>
        <input type="number" name="filter[id]" id="form-filter-id" class="form-control" placeholder="<?= $customerView->translate('Customer ID') ?>" value="<?= $this->escape($filters['id']) ?>"<?= $filterDefinition->isLocked('id') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-email"><?= $customerView->translate('E-Mail') ?></label>
        <input type="text" name="filter[email]" id="form-filter-email" class="form-control" placeholder="<?= $customerView->translate('E-Mail') ?>" value="<?= $this->escape($filters['email']) ?>"<?= $filterDefinition->isLocked('email') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-name"><?= $customerView->translate('Name') ?></label>
        <input type="text" name="filter[name]" id="form-filter-name" class="form-control" placeholder="<?= $customerView->translate('Name') ?>" value="<?= $this->escapeFormValue($filters['name']) ?>"<?= $filterDefinition->isLocked('name') ? ' disabled' : '' ?>>
    </div>
</fieldset>

<fieldset>
    <legend>
        <?= $customerView->translate('Options') ?>
    </legend>

    <div class="form-group">
        <div class="checkbox plugin-icheck">
            <label>
                <input name="filter[active]" value="1" type="checkbox" <?= $filters['active'] ? 'checked="checked"' : '' ?>>
                <?= $customerView->translate('Show only active users') ?>
            </label>
        </div>
    </div>
</fieldset>