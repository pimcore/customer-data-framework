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

$this->jsConfig()->add('selectedSegmentsChanged', true);
?>
<!-- Filters -->
<div class="box box-default box-collapsible-state search-filters-box" data-identifier="<?= $identifier ?>">
    <div class="box-header with-border">
        <div class="row">
            <div class="col-xs-3 col-md-8">
                <h3 class="box-title">
                    <a href="#" data-widget="collapse-trigger">
                        <i class="fa fa-filter"></i>
                        <?= $customerView->translate('Filters'); ?>
                    </a>
                </h3>
            </div>

            <div class="col-xs-9 col-md-4 text-right">
                <button type="button" class="btn btn-primary"><?= $customerView->translate('New Customer'); ?></button>

                <select
                        id="filterDefinition[id]"
                        name="filterDefinition[id]"
                        onchange="this.form.submit()"
                        class="form-control plugin-select2"
                        data-select2-options='<?= json_encode(['width' => '70%']) ?>'
                >
                    <option value="0"><?= $customerView->translate('No filter') ?></option>
                    <?php
                    foreach ($filterDefinitions as $singleFilterDefinition): ?>
                        <option value="<?= $singleFilterDefinition->getId() ?>"<?= ($singleFilterDefinition->getId() == $filterDefinition->getId()) ? " selected" : "" ?>><?= $singleFilterDefinition->getName(); ?></option>
                    <?php endforeach; ?>
                </select>

                <?php if (\Pimcore\Tool\Admin::getCurrentUser()->isAllowed('plugin_cmf_perm_customerview_admin')): ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#show-segments-modal"><?= $customerView->translate('Edit'); ?>
                    </button>
                    <div id="show-segments-modal" class="modal fade text-left" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title"><?= $customerView->translate('Select your segments') ?></h4>
                                </div>
                                <div class="modal-body">
                                    <?php foreach ($segmentGroups as $segmentGroup): ?>
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <div class="checkbox plugin-icheck">
                                                    <label>
                                                        <input name="filter[showSegments][]"
                                                               value="<?= $segmentGroup->getId() ?>"
                                                               type="checkbox"
                                                            <?= (in_array($segmentGroup->getId(),
                                                                $filters['showSegments'])) ? ' checked="checked"' : '' ?>><?= $segmentGroup->getName() ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="modal-footer">
                                    <input type="submit" class="btn btn-primary"
                                           value="<?= $customerView->translate('Apply'); ?>"
                                           name="apply-segment-selection"/>
                                    <input type="button" class="btn btn-default"
                                           value="<?= $customerView->translate('Close'); ?>"
                                           data-dismiss="modal"/>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <a class="btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></a>

            </div>
        </div>

    </div>
    <!-- /.box-header -->

    <div class="box-body">

