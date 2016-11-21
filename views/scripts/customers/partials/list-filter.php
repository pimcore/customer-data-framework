<form class="search-filters" role="form" action="<?= $this->filterFormAction($this->paginator) ?>">

    <div class="box box-default box-collapsible-state search-filters-box search-filters-box--standalone" data-identifier="customer-search-bar">
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <input type="text" name="filter[search]" class="form-control" placeholder="Search..." value="<?= $this->formFilterValue('search') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->template('partials/filter/box/header.php', ['identifier' => 'customer-search-filter']) ?>

        <div class="row">
            <div class="col-md-4">
                <fieldset>
                    <legend>
                        Customer
                    </legend>

                    <div class="form-group">
                        <label for="form-filter-id">Customer ID</label>
                        <input type="number" name="filter[id]" id="form-filter-id" class="form-control" placeholder="Customer ID" value="<?= $this->formFilterValue('id') ?>">
                    </div>

                    <div class="form-group">
                        <label for="form-filter-email">E-Mail</label>
                        <input type="text" name="filter[email]" id="form-filter-email" class="form-control" placeholder="E-Mail" value="<?= $this->formFilterValue('email') ?>">
                    </div>

                    <div class="form-group">
                        <label for="form-filter-name">Name</label>
                        <input type="text" name="filter[name]" id="form-filter-name" class="form-control" placeholder="Name" value="<?= $this->formFilterValue('name') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>
                        Options
                    </legend>

                    <div class="form-group">
                        <div class="checkbox plugin-icheck">
                            <label>
                                <input name="filter[active]" value="1" type="checkbox" <?= $this->formFilterCheckedState('active', 1) ?>>
                                Show only active users
                            </label>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-md-4">

                <?= $this->template('customers/partials/list-filter/segments.php') ?>

            </div>
        </div>

    <?= $this->template('partials/filter/box/footer.php') ?>

</form>
