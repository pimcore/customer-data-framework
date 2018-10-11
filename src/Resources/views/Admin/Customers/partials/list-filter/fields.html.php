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
    </legend>

    <div class="row">
        <div class="col-md-6">
            <div class="dropdown plugin-icheck">
                <div class="form-group">
                    <?php $or = $customerView->translate('cmf_filters_options_or');?>
                    <?php $and = $customerView->translate('cmf_filters_options_and');?>
                    <label for="form-filter-operator-customer"><?= $customerView->translate('cmf_filters_options_operator') ?></label>
                    <select
                            id="form-filter-operator-customer"
                            name="filter[operator-customer]"
                            class="form-control plugin-select2"
                            data-placeholder="<?= $customerView->translate('cmf_filters_options_operator')  ?>">

                        <option <?= $filters['operator-customer'] == 'AND' ? 'selected="selected"' : '' ?> value="AND"><?= $and ?></option>
                        <option <?= $filters['operator-customer'] == 'OR' ? 'selected="selected"' : '' ?> value="OR"><?= $or ?></option>

                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="form-filter-id"><?= $customerView->translate('cmf_filters_customer_id') ?></label>
        <input type="number" name="filter[id]" id="form-filter-id" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_id') ?>" value="<?= $this->escape($filters['id']) ?>"<?= $filterDefinition->isLocked('id') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-email"><?= $customerView->translate('cmf_filters_customer_email') ?></label>
        <input type="text" name="filter[email]" id="form-filter-email" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_email') ?>" value="<?= $this->escape($filters['email']) ?>"<?= $filterDefinition->isLocked('email') ? ' disabled' : '' ?>>
    </div>

    <div class="form-group">
        <label for="form-filter-name"><?= $customerView->translate('cmf_filters_customer_name') ?></label>
        <input type="text" name="filter[name]" id="form-filter-name" class="form-control" placeholder="<?= $customerView->translate('cmf_filters_customer_name') ?>" value="<?= $this->escapeFormValue($filters['name']) ?>"<?= $filterDefinition->isLocked('name') ? ' disabled' : '' ?>>
    </div>
</fieldset>

<fieldset>
    <legend>
        <?= $customerView->translate('cmf_filters_options') ?>
    </legend>

    <div class="form-group">
        <div class="checkbox plugin-icheck">
            <label>
                <input name="filter[active]" value="1" type="checkbox" <?= $filters['active'] ? 'checked="checked"' : '' ?>>
                <?= $customerView->translate('cmf_filters_options_only_active') ?>
            </label>
        </div>
    </div>
</fieldset>