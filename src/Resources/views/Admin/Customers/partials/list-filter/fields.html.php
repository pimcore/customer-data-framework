<?php
/**
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 * @var array $filters
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition $filterDefinition
 */
?>

<fieldset>
    <legend>
        <?= $customerView->translate('cmf_filters_customer') ?>
        <?php $or = $customerView->translate('cmf_filters_options_any');?>
        <?php $and = $customerView->translate('cmf_filters_options_and');?>
        <select
                id="form-filter-operator-customer"
                name="filter[operator-customer]"
                class="form-filter-operator"
                data-placeholder="<?= $customerView->translate('cmf_filters_options_operator')  ?>">

            <option <?= $filters['operator-customer'] ?? null == 'AND' ? 'selected="selected"' : '' ?> value="AND"><?= $and ?></option>
            <option <?= $filters['operator-customer'] ?? null  == 'OR' ? 'selected="selected"' : '' ?> value="OR"><?= $or ?></option>

        </select>
    </legend>

    <div class="form-group">
        <label for="form-filter-id"><?= $customerView->translate('cmf_filters_customer_id') ?></label>
        <input type="number" autocomplete="no" name="filter[id]" id="form-filter-id" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_id') ?>" value="<?= $this->escape($filters['id'] ?? null) ?>"<?= $filterDefinition->isLocked('id') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-email"><?= $customerView->translate('cmf_filters_customer_email') ?></label>
        <input type="text" autocomplete="no" name="filter[email]" id="form-filter-email" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_email') ?>" value="<?= $this->escape($filters['email'] ?? null) ?>"<?= $filterDefinition->isLocked('email') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-firstname"><?= $customerView->translate('cmf_filters_customer_firstname') ?></label>
        <input type="text" autocomplete="no" name="filter[firstname]" id="form-filter-firstname" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_firstname') ?>" value="<?= $this->escapeFormValue($filters['firstname'] ?? null) ?>"<?= $filterDefinition->isLocked('firstname') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-lastname"><?= $customerView->translate('cmf_filters_customer_lastname') ?></label>
        <input type="text" autocomplete="no" name="filter[lastname]" id="form-filter-lastname" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_lastname') ?>" value="<?= $this->escapeFormValue($filters['lastname'] ?? null) ?>"<?= $filterDefinition->isLocked('lastname') ? ' disabled' : '' ?>>
    </div>
</fieldset>

<fieldset>
    <legend>
        <?= $customerView->translate('cmf_filters_options') ?>
    </legend>

    <div class="form-group">
        <div class="checkbox plugin-icheck">
            <label>
                <input name="filter[active]" value="1" type="checkbox" <?= isset($filters['active']) ? 'checked="checked"' : '' ?>>
                <?= $customerView->translate('cmf_filters_options_only_active') ?>
            </label>
        </div>
    </div>
</fieldset>
