<?php
/**
 * @var \Zend_Paginator $paginator
 */
$paginator = $this->activities;
$av = \CustomerManagementFramework\Factory::getInstance()->getActivityView();

$contentBoxClasses = [];
if (sizeof($paginator)) {
    $contentBoxClasses = ['no-padding', 'table-responsive'];
}
?>

<section class="content">
    <div class="box box-info">
        <div class="box-header with-border with-form">
            <h3 class="box-title">
                <?=$av->translate("Activities")?>
            </h3>

            <div class="box-tools pull-right">
                <form>
                    <div class="form-group">
                        <select name="type" class="form-control js-form-auto-submit">
                            <option value=""><?= $av->translate("all activity types") ?></option>
                            <?php foreach ($this->types as $type): ?>
                                <option value="<?= $type ?>"<?php if ($this->type == $type) { ?> selected="selected"<?php } ?>><?= $av->translate($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="box-body <?= implode(' ', $contentBoxClasses) ?>">
            <?php if (sizeof($paginator)): ?>
                <table class="table table-striped table-bordered table-hover">
                    <tr>
                        <th><?=$av->translate("Activity")?></th>
                        <th><?=$av->translate("Activity date")?></th>
                        <th><?=$av->translate("Activity details")?></th>
                    </tr>

                    <?php foreach($this->activities as $activity) {
                        /**
                         * @var \CustomerManagementFramework\ActivityStoreEntry\DefaultActivityStoreEntry $activity
                         */
                        ?>

                        <tr>
                            <td width="300">
                                <?=$av->translate($activity->getType())?>
                                <a href="<?=$this->url(['controller'=>'activities','action'=>'detail','activityId'=>$activity->getId(),'customerId'=>$this->customer->getId()])?>" class="btn btn-default btn-xs"><?=$av->translate("Details")?></a>
                            </td>
                            <td width="160"><?=$activity->getActivityDate()->formatLocalized("%x %X")?></td>
                            <td>
                                <?php if($data = $av->getOverviewAdditionalData($activity)) {?>
                                    <table class="overview-data-table">
                                        <tr>
                                            <?php foreach($data as $key => $value) {?>
                                                <th><?=$key?></th>
                                            <?php }?>
                                        </tr>
                                        <tr>
                                            <?php foreach($data as $key => $value) {?>
                                                <td><?=$value?></td>
                                            <?php }?>
                                        </tr>
                                    </table>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php }?>

                </table>

                <?php if($paginator->getPages()->pageCount > 1): ?>
                    <div class="text-center">
                        <?= $this->paginationControl($paginator, 'Sliding', 'includes/pagination/default.php', ['params'=>$this->getAllParams()]); ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>

                <div class="callout callout-warning">
                    <p><?=$av->translate("no activities found")?></p>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>
