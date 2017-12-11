<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 *
 * @var \Pimcore\Model\DataObject\CustomerSegmentGroup[] $segmentGroups
 * @var string $identifier
 * @var array $filters
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition[] $filterDefinitions
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition $filterDefinition
 */
?>
<!-- Filters -->
<div class="box box-default box-collapsible-state search-filters-box" data-identifier="<?= $identifier ?>">
    <div class="box-header with-border">
        <div class="row">
            <div class="col-sm-3 col-md-6">
                <h3 class="box-title">
                    <a href="#" data-widget="collapse-trigger">
                        <i class="fa fa-filter"></i>
                        <?= $customerView->translate('Filters'); ?>
                    </a>
                </h3>
            </div>

            <div class="col-sm-9 col-md-6 text-right">
                <button type="button" class="btn btn-primary"><?= $customerView->translate('New Customer'); ?></button>

                <select
                        id="filterDefinition[id]"
                        name="filterDefinition[id]"
                        onchange="this.form.submit()"
                        class="form-control plugin-select2"
                        data-select2-options='<?= json_encode(['width' => '50%']) ?>'
                >
                    <option value="0"><?= $customerView->translate('No filter') ?></option>
                    <?php
                    foreach ($filterDefinitions as $singleFilterDefinition): ?>
                        <option value="<?= $singleFilterDefinition->getId() ?>"<?= ($singleFilterDefinition->getId() == $filterDefinition->getId()) ? " selected" : "" ?>><?= $singleFilterDefinition->getName(); ?></option>
                    <?php endforeach; ?>
                </select>

                <a class="btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></a>

            </div>
        </div>

    </div>
    <!-- /.box-header -->

    <div class="box-body">

