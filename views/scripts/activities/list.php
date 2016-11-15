<?php
    $this->headLink()->appendStylesheet('/plugins/CustomerManagementFramework/static/css/activity-list.css');
    /**
     * @var \Zend_Paginator $paginator
     */
    $paginator = $this->activities;
    $av = \CustomerManagementFramework\Factory::getInstance()->getActivityView();
?>

<div class="container-fluid">
    <h2><?=$av->translate("Activities")?></h2>

    <div class="row mb15">
        <form>
            <div class="col col-sm-3">

                <div class="input-group">
                    <?php $this->jsConfig()->add("formAutoSubmit",true)?>
                    <select name="type" class="form-control js-form-auto-submit">
                        <option value=""><?=$av->translate("all activity types")?></option>
                        <?php foreach($this->types as $type) {?>
                        <option value="<?=$type?>"<?php if($this->type == $type) {?> selected="selected"<?php }?>><?=$av->translate($type)?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <?if(sizeof($paginator)) {?>
        <?php
        $av = \CustomerManagementFramework\Factory::getInstance()->getActivityView();
        ?>
        <table class="table table-striped table-hover">
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
                    <td width="200">
                        <?=$av->translate($activity->getType())?>
                        <a href="<?=$this->url(['controller'=>'activities','action'=>'detail','activityId'=>$activity->getId(),'customerId'=>$this->customer->getId()])?>" class="btn btn-default btn-xs"><?=$av->translate("Details")?></a>
                    </td>
                    <td width="200"><?=$activity->getActivityDate()->formatLocalized("%x %X")?></td>
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
    <?php } else {?>
        <div class="alert alert-warning">
            <?=$av->translate("no activities found")?>
        </div>

    <?php }?>

</div>

