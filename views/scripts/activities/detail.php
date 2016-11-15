<?php
$this->headLink()->appendStylesheet('/plugins/CustomerManagementFramework/static/css/activity-list.css');
/**
 * @var \CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface $activity
 */
$activity = $this->activity;

?>

<?php if($activity) {?>

    <?php if($template = \CustomerManagementFramework\Factory::getInstance()->getActivityView()->getDetailviewTemplate($activity)) { ?>
        <?$this->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts')?>
        
        <?$this->template($template)?>
    <?php } else {?>

        <?php $visibleAttributes = \CustomerManagementFramework\Factory::getInstance()->getActivityView()->getDetailviewData($activity); ?>
        <div class="container-fluid">
            <h2><?=$activity->getType()?></h2>

            <div class="mb15">
                <a href="<?=$this->url(["module" => "CustomerManagementFramework", "controller"=>"activities","action"=>"list","customerId"=>$this->getParam("customerId")],null,true)?>" class="btn btn-default btn-xs">&laquo; back</a>
            </div>
            <div class="row">
                <?php foreach(array_chunk($visibleAttributes, ceil(sizeof($visibleAttributes)/2), true) as $attributes) {?>
                <div class="col col-sm-6">
                    <table class="table table-striped">

                            <?php foreach($attributes as $key => $value) {?>
                                <tr>
                                    <th width="200"><?=$key?></th>
                                    <td>
                                        <?=$value?>
                                    </td>
                                </tr>
                            <?php }?>

                    </table>
                </div>
                <?php }?>
            </div>
        </div>

    <?php } ?>

<?php } ?>