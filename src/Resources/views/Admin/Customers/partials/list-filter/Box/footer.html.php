<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 *
 * @var array $clearUrlParams
 * @var \Pimcore\Model\DataObject\CustomerSegmentGroup[] $segmentGroups
 * @var array $filters
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition $filterDefinition
 */
?>

        </div>
        <!-- /.box-body -->

        <div class="box-footer text-right">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#save-filter-definition-modal"><i class="fa fa-save"></i>&nbsp;<?= $customerView->translate('Save Filter') ?></button>
            <div id="save-filter-definition-modal" class="modal fade text-left" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title pull-left"><?= $customerView->translate('Save your filter') ?></h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label for="filter_definition_name"><?= $customerView->translate('Name') ?></label>
                                        <input type="text" name="filter_definition_name" id="filter_definition_name" class="form-control" placeholder="<?= $customerView->translate('Name') ?>" value="<?= $this->escapeFormValue($filterDefinition->getName()) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="checkbox plugin-icheck">
                                        <label for="filter_definition_readonly">
                                            <input type="checkbox" name="filter_definition_readonly" id="filter_definition_readonly">
                                            <?= $customerView->translate('Read Only'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="checkbox plugin-icheck">
                                        <label for="filter_definition_shortcut_available">
                                            <input type="checkbox" name="filter_definition_shortcut_available" id="filter_definition_shortcut_available">
                                            <?= $customerView->translate('Shortcut Available'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" class="btn btn-primary"  value="Save" name="save-filter-definition"/>
                            <input type="button" class="btn btn-default" value="Close" data-dismiss="modal"/>
                        </div>
                    </div>
                </div>
            </div>

            <a href="<?= $this->selfUrl()->get(true, $this->addPerPageParam()->add($clearUrlParams ?: [])) ?>"
               class="btn btn-default">
                <i class="fa fa-ban"></i>
                Clear Filters
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-filter"></i>
                Apply Filters
            </button>
        </div>
        <!-- /.box-footer -->

    </form>
</div>
