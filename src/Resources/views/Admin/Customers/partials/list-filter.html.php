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
                            <input type="text" name="filter[search]" class="form-control" placeholder="<?= $cv->translate('cmf_filters_search') ?>..." value="<?= $this->filters['search'] ?>">
                            <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:search-bar-help.html.php') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter/Box:header.html.php', ['identifier' => 'customer-search-filter']) ?>

    <?= $this->template($cv->getFilterWrapperTemplate()) ?>

    <?php if (sizeof($this->clearUrlParams)) {
    ?>
        <?php foreach ($this->clearUrlParams as $key => $value) {
        ?>
            <input type="hidden" name="<?=$key?>" value="<?=$value?>"/>
        <?php
    } ?>
    <?php
} ?>

    <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter/Box:footer.html.php') ?>

