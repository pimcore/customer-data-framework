<?php
/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<form class="search-filters" role="form" action="<?= $this->filterFormAction()->get($this->paginator) ?>">

    <?php if (count($this->searchBarFields) > 0): ?>
        <div class="box box-default box-collapsible-state search-filters-box search-filters-box--standalone" data-identifier="customer-search-bar">
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group" id="searchBar">
                            <input type="text" name="filter[search]" class="form-control" placeholder="<?= $cv->translate('Search') ?>..." value="<?= $this->filters['search'] ?>">
                            <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:search-bar-help.html.php') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Partial/Box:header.html.php', ['identifier' => 'customer-search-filter']) ?>

        <div class="row">
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        <?= $cv->translate('Customer') ?>
                    </legend>

                    <div class="form-group">
                        <label for="form-filter-id"><?= $cv->translate('Customer ID') ?></label>
                        <input type="number" name="filter[id]" id="form-filter-id" class="form-control" placeholder="<?= $cv->translate('Customer ID') ?>" value="<?= $this->escape($this->filters['id']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="form-filter-email"><?= $cv->translate('E-Mail') ?></label>
                        <input type="text" name="filter[email]" id="form-filter-email" class="form-control" placeholder="<?= $cv->translate('E-Mail') ?>" value="<?= $this->escape($this->filters['email']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="form-filter-name"><?= $cv->translate('Name') ?></label>
                        <input type="text" name="filter[name]" id="form-filter-name" class="form-control" placeholder="<?= $cv->translate('Name') ?>" value="<?= $this->escapeFormValue($this->filters['name']) ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        <?= $cv->translate('Options') ?>
                    </legend>

                    <div class="form-group">
                        <div class="checkbox plugin-icheck">
                            <label>
                                <input name="filter[active]" value="1" type="checkbox" <?= $this->filters['active'] ? 'checked="checked"' : '' ?>>
                                <?= $cv->translate('Show only active users') ?>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-md-8">

                <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:segments.html.php') ?>

            </div>
        </div>

    <?php if (sizeof($this->clearUrlParams)) {
    ?>
        <?php foreach ($this->clearUrlParams as $key => $value) {
        ?>
            <input type="hidden" name="<?=$key?>" value="<?=$value?>"/>
        <?php
    } ?>
    <?php
} ?>

    <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Partial/Box:footer.html.php') ?>

