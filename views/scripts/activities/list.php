<?php
    $this->headLink()->appendStylesheet('/plugins/CustomerManagementFramework/static/css/activity-list.css');
    $paginator = $this->activities;
?>

<div class="container-fluid">
    <h2>Activities</h2>

    <?if(sizeof($paginator)) {?>
        <table class="table table-striped table-hover">
            <tr>
                <th>
                    Activity
                </th>
                <th>
                    Activity Date
                </th>
                <th>
                    Activity Details
                </th>
            </tr>
            <?php foreach($this->activities as $activity) {
                /**
                 * @var \CustomerManagementFramework\ActivityStoreEntry\DefaultActivityStoreEntry $activity
                 */
                ?>
                <tr>
                    <td width="200">
                        <?=$activity->getType()?>
                        <a href="<?=$this->url(['controller'=>'activities','action'=>'detail','activityId'=>$activity->getId()])?>" class="btn btn-default btn-xs">Details</a>
                    </td>
                    <td width="200"><?=$activity->getActivityDate()?></td>
                    <td>
                        <?php if($data = \CustomerManagementFramework\Factory::getInstance()->getActivityView()->getOverviewAdditionalData($activity)) {?>
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
            no activities found
        </div>

    <?php }?>

</div>

