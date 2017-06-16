<?php
/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<form class="search-filters" role="form" action="<?= $this->filterFormAction($this->paginator) ?>">

    <?php if (count($this->searchBarFields) > 0): ?>
        <div class="box box-default box-collapsible-state search-filters-box search-filters-box--standalone" data-identifier="customer-search-bar">
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group" id="searchBar">
                            <input type="text" name="filter[search]" class="form-control" placeholder="<?= $cv->translate('Search') ?>..." value="<?= $this->formFilterValue('search') ?>">
                            <?= $this->template('customers/partials/list-filter/search-bar-help.php') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->template('partials/filter/box/header.php', ['identifier' => 'customer-search-filter']) ?>

        <div class="row">
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        <?= $cv->translate('Customer') ?>
                    </legend>

                    <div class="form-group">
                        <label for="form-filter-id"><?= $cv->translate('Customer ID') ?></label>
                        <input type="number" name="filter[id]" id="form-filter-id" class="form-control" placeholder="<?= $cv->translate('Customer ID') ?>" value="<?= $this->formFilterValue('id') ?>">
                    </div>

                    <div class="form-group">
                        <label for="form-filter-email"><?= $cv->translate('E-Mail') ?></label>
                        <input type="text" name="filter[email]" id="form-filter-email" class="form-control" placeholder="<?= $cv->translate('E-Mail') ?>" value="<?= $this->formFilterValue('email') ?>">
                    </div>

                    <div class="form-group">
                        <label for="form-filter-name"><?= $cv->translate('Name') ?></label>
                        <input type="text" name="filter[name]" id="form-filter-name" class="form-control" placeholder="<?= $cv->translate('Name') ?>" value="<?= $this->formFilterValue('name') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        <?= $cv->translate('Options') ?>
                    </legend>

                    <div class="form-group">
                        <div class="checkbox plugin-icheck">
                            <label>
                                <input name="filter[active]" value="1" type="checkbox" <?= $this->formFilterCheckedState('active', 1) ?>>
                                <?= $cv->translate('Show only active users') ?>
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-md-8">

                <?= $this->template('customers/partials/list-filter/segments.php') ?>

            </div>
        </div>

    <?= $this->template('partials/filter/box/footer.php') ?>

</form>
