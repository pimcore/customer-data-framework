<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 * @var array $clearUrlParams
 * @var \Pimcore\Model\DataObject\CustomerSegmentGroup[] $segmentGroups
 * @var array $filters
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition $filterDefinition
 * @var bool $hideAdvancedFilterSettings
 */

$userAllowedToUpdate = ($filterDefinition->getId() && $filterDefinition->isUserAllowedToUpdate(\Pimcore\Tool\Admin::getCurrentUser()));
$userAllowedToShare = ($filterDefinition->getId() && $filterDefinition->isUserAllowedToShare(\Pimcore\Tool\Admin::getCurrentUser()));

/** @noinspection PhpUndefinedMethodInspection */
$this->jsConfig()->add('registerSaveFilterDefinition', true);
/** @noinspection PhpUndefinedMethodInspection */
$this->jsConfig()->add('registerUpdateFilterDefinition', true);
/** @noinspection PhpUndefinedMethodInspection */
$this->jsConfig()->add('registerShareFilterDefinition', true);
?>

</div>
<!-- /.box-body -->

<div class="box-footer text-right">
    <?php
    // check if user is only allowed to share filter
    if(!$hideAdvancedFilterSettings && $userAllowedToShare && !$userAllowedToUpdate) :
        ?>
        <button type="button" class="btn btn-default" data-toggle="modal"
                data-target="#share-filter-definition-modal">
            <i class="fa fa-share"></i>&nbsp;<?= $customerView->translate('cmf_filters_share') ?></button>
        <div id="share-filter-definition-modal" class="modal fade text-left" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title pull-left"><?= $customerView->translate('cmf_filters_share_headline') ?></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:user-roles.html.php',
                            ['preselected' => false]) ?>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal"/>
                        <a type="button" class="btn btn-primary" name="share-filter-definition"
                           id="share-filter-definition">
                            <?= $customerView->translate('cmf_filters_share_confirm'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // check if user has update permission on filter
    if(!$hideAdvancedFilterSettings) : ?>
        <?php if($userAllowedToUpdate): ?>
            <button type="button" class="btn btn-danger" data-toggle="modal"
                    data-target="#delete-filter-definition-modal">
                <i class="fa fa-trash"></i>&nbsp;<?= $customerView->translate('cmf_filters_delete') ?></button>
            <div id="delete-filter-definition-modal" class="modal fade text-left" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title pull-left"><?= $customerView->translate('cmf_filters_delete_headline') ?></h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-footer">
                            <input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal"/>
                            <a class="btn btn-danger" href="<?= $this->url('cmf_filter_definition_delete',
                                ['filterDefinition' => ['id' => $filterDefinition->getId()]]); ?>"
                               name="delete-filter-definition">
                                <?= $customerView->translate('cmf_filters_delete_confirm') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#save-filter-definition-modal"><i
                    class="fa fa-save"></i>&nbsp;<?= ($userAllowedToUpdate && $userAllowedToShare) ? $customerView->translate('cmf_filters_save_share') : $customerView->translate('cmf_filters_save') ?>
        </button>
        <div id="save-filter-definition-modal" class="modal fade text-left" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title pull-left"><?= $customerView->translate('cmf_filters_save_headline') ?></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">

                        <div class="alert alert-danger" style="display: none;" id="name-required-message">
                            <?= $customerView->translate('cmf_filters_save_error_name') ?>
                        </div>

                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="filterDefinition[name]"><?= $customerView->translate('cmf_filters_save_name') ?></label>
                                    <input type="text" name="filterDefinition[name]" id="filterDefinition[name]"
                                           class="form-control"
                                           placeholder="<?= $customerView->translate('cmf_filters_save_name') ?>"
                                           value="<?= $filterDefinition->getName() ?>">
                                </div>
                            </div>
                        </div>

                        <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:user-roles.html.php',['preselected' => true]) ?>

                        <div class="row">
                            <div class="col-xs-6">
                                <div class="checkbox plugin-icheck">
                                    <label for="filterDefinition[readOnly]">
                                        <input type="checkbox" name="filterDefinition[readOnly]"
                                               id="filterDefinition[readOnly]"<?= $filterDefinition->isReadOnly() ? ' checked="checked"' : '' ?>>
                                        <?= $customerView->translate('cmf_filters_save_read_only'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="checkbox plugin-icheck">
                                    <label for="filterDefinition[shortcutAvailable]">
                                        <input type="checkbox" name="filterDefinition[shortcutAvailable]"
                                               id="filterDefinition[shortcutAvailable]"<?= $filterDefinition->isShortcutAvailable() ? ' checked="checked"' : '' ?>>
                                        <?= $customerView->translate('cmf_filters_save_shortcut_available'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-default" value="<?= $customerView->translate('cmf_filters_cancel'); ?>"
                               data-dismiss="modal"/>
                        <?php if($userAllowedToUpdate): ?>
                            <a type="button" class="btn btn-primary" name="update-filter-definition"
                               id="update-filter-definition"><i class="fa fa-save"></i>
                                <?= $customerView->translate('cmf_filters_save_update'); ?>
                            </a>
                        <?php endif; ?>
                        <a type="button" class="btn btn-success" name="save-filter-definition"
                           id="save-filter-definition"><i class="fa fa-plus"></i>
                            <?= $customerView->translate('cmf_filters_save_new'); ?>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <a href="<?= $this->selfUrl()->get(true, $this->addPerPageParam()->add($clearUrlParams ?: [])) ?>"
       class="btn btn-default">
        <i class="fa fa-ban"></i>
        <?= $customerView->translate('cmf_filters_clear'); ?>
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-filter"></i>
        <?= $customerView->translate('cmf_filters_apply'); ?>
    </button>
</div>
<!-- /.box-footer -->

</form>
</div>
