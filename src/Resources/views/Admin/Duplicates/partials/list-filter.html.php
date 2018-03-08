<form class="search-filters" role="form" action="<?= $this->filterFormAction()->get($this->paginator) ?>">

    <div class="box box-default box-collapsible-state search-filters-box search-filters-box--standalone" data-identifier="customer-search-bar">
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group" id="searchBar">
                        <input type="text" name="filter[search]" class="form-control" placeholder="<?= $this->duplicatesView->translate('cmf_filters_search') ?>..." value="<?= $this->filters['search'] ?>">
                        <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Duplicates/partials:list-filter-helper.html.php') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="declined" value="<?=$this->getParam('declined')?>"/>

</form>