<?= $this->template('partials/filter/box/header.php', ['identifier' => 'user-search-filter']) ?>

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
</div>

<?= $this->template('partials/filter/box/footer.php') ?>
