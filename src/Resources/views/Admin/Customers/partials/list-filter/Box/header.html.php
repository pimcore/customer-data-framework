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
 */

$this->jsConfig()->add('selectedSegmentsChanged', true);
?>
<!-- Filters -->
<div class="box box-default box-collapsible-state search-filters-box" data-identifier="<?= $identifier ?>">
    <div class="box-header with-border">
        <h3 class="box-title">
            <a href="#" data-widget="collapse-trigger">
                <i class="fa fa-filter"></i>
                Filters
            </a>
        </h3>


        <div class="pull-right">
            <a class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </a>
        </div>

        <?php if (\Pimcore\Tool\Admin::getCurrentUser()->isAllowed('plugin_cmf_perm_customerview_admin')): ?>
            <div class="pull-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#show-segments-modal">Edit</button>
                <div id="show-segments-modal" class="modal fade" role="dialog">
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
                                <input type="submit" class="btn btn-primary"  value="Apply" name="apply-segment-selection"/>
                                <input type="button" class="btn btn-default" value="Close" data-dismiss="modal"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="pull-right">
            <select class="form-control" id="filter[definition]" name="filter[definition]"
                    onchange="this.form.submit()">
                <option value="0">No filter</option>
                <?php
                foreach ($filterDefinitions as $filterDefinition): ?>
                    <option value="<?= $filterDefinition->getId() ?>"<?= ($filters['definition'] == $filterDefinition->getId()) ? " selected" : "" ?>><?= $filterDefinition->getName(); ?></option>
                <?php endforeach; ?>
            </select>
        </div>


    </div>
    <!-- /.box-header -->

    <div class="box-body">

