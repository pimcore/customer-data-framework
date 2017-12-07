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
    <?php if (\Pimcore\Tool\Admin::getCurrentUser()->isAllowed('plugin_cmf_perm_customerview_admin')): ?>
        <?php if ($filterDefinition->getId()): ?>
            <button type="button" class="btn btn-danger" data-toggle="modal"
                    data-target="#delete-filter-definition-modal">
                <i class="fa fa-trash"></i>&nbsp;<?= $customerView->translate('Delete Filter') ?></button>
            <div id="delete-filter-definition-modal" class="modal fade text-left" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title pull-left"><?= $customerView->translate('Delete filter?') ?></h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" class="btn btn-danger" value="Delete" name="delete-filter-definition"/>
                            <input type="button" class="btn btn-default" value="Close" data-dismiss="modal"/>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#save-filter-definition-modal"><i
                    class="fa fa-save"></i>&nbsp;<?= $customerView->translate('Save Filter') ?></button>
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
                                    <label for="filterDefinition[name]"><?= $customerView->translate('Name') ?></label>
                                    <input type="text" name="filterDefinition[name]" id="filterDefinition[name]"
                                           class="form-control" placeholder="<?= $customerView->translate('Name') ?>"
                                           value="<?= $this->escapeFormValue($filterDefinition->getName()) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="filterDefinition[allowedUserIds]"><?= $customerView->translate('Share with user') ?></label>
                                    <select
                                            id="filterDefinition[allowedUserIds]"
                                            name="filterDefinition[allowedUserIds][]"
                                            class="form-control plugin-select2"
                                            multiple="multiple"
                                            data-placeholder=""
                                            data-select2-options='<?= json_encode(['allowClear' => false]) ?>'>
                                        <?php
                                        $users = (new \Pimcore\Model\User\Listing())->load();

                                        /** @var Pimcore\Model\User $user */
                                        foreach ($users as $user):
                                            if($user->getType() !== 'user') continue;
                                            ?>

                                            <option value="<?= $user->getId() ?>"<?= in_array($user->getId(), $filterDefinition->getAllowedUserIds()) ? ' selected="selected"' : '' ?>>
                                                <?= !empty($user->getFirstname().$user->getLastname()) ? trim($user->getFirstname() . ' ' . $user->getLastname()) : $user->getName() ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="filterDefinition[allowedRoleIds]"><?= $customerView->translate('Share with user') ?></label>
                                    <select
                                            id="filterDefinition[allowedRoleIds]"
                                            name="filterDefinition[allowedRoleIds][]"
                                            class="form-control plugin-select2"
                                            multiple="multiple"
                                            data-placeholder=""
                                            data-select2-options='<?= json_encode(['allowClear' => false]) ?>'>
                                        <?php
                                        $roles = (new \Pimcore\Model\User\Role\Listing())->load();

                                        /** @var Pimcore\Model\User\Role $role */
                                        foreach ($roles as $role): ?>

                                            <option value="<?= $role->getId() ?>"<?= in_array($role->getId(), $filterDefinition->getAllowedUserIds()) ? ' selected="selected"' : '' ?>>
                                                <?= $role->getName() ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="checkbox plugin-icheck">
                                    <label for="filterDefinition[readOnly]">
                                        <input type="checkbox" name="filterDefinition[readOnly]"
                                               id="filterDefinition[readOnly]"<?= $filterDefinition->isReadOnly() ? ' checked="checked"' : '' ?>>
                                        <?= $customerView->translate('Read Only'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="checkbox plugin-icheck">
                                    <label for="filterDefinition[shortcutAvailable]">
                                        <input type="checkbox" name="filterDefinition[shortcutAvailable]"
                                               id="filterDefinition[shortcutAvailable]"<?= $filterDefinition->isShortcutAvailable() ? ' checked="checked"' : '' ?>>
                                        <?= $customerView->translate('Shortcut Available'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-primary" value="<?= $customerView->translate('Save'); ?>" name="save-filter-definition"/>
                        <input type="button" class="btn btn-default" value="<?= $customerView->translate('Close'); ?>" data-dismiss="modal"/>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <a href="<?= $this->selfUrl()->get(true, $this->addPerPageParam()->add($clearUrlParams ?: [])) ?>"
       class="btn btn-default">
        <i class="fa fa-ban"></i>
        <?= $customerView->translate('Clear Filters'); ?>
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-filter"></i>
        <?= $customerView->translate('Apply Filters'); ?>
    </button>
</div>
<!-- /.box-footer -->

</form>
</div>
