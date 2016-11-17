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
        <fieldset>
            <legend>
                Segments
            </legend>

            <?php
            /** @var \Pimcore\Model\Object\CustomerSegment[] $segments */
            foreach ($this->segments as $groupName => $segments): ?>

                <div class="form-group">
                    <label for="form-filter-<?= $groupName ?>"><?= $groupName ?></label>
                    <select id="form-filter-<?= $groupName ?>" name="filter[<?= $groupName ?>][]" class="form-control plugin-select2" multiple="multiple" data-placeholder="<?= $groupName ?>">

                        <?php foreach ($segments as $segment): ?>

                            <option value="<?= $segment->getId() ?>" <?= $this->formFilterSelectedState($groupName, $segment->getId(), true) ?>>
                                <?= $segment->getName() ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

            <?php endforeach; ?>

        </fieldset>

    </div>
</div>

<?= $this->template('partials/filter/box/footer.php') ?>
