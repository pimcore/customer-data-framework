<?php
$this->headLink()->appendStylesheet('/plugins/CustomerManagementFramework/static/css/activity-list.css');
/**
 * @var \CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface $activity
 */
$activity = $this->activity;
?>

<?php if ($activity): ?>

    <section class="content">

        <?php if ($template = \CustomerManagementFramework\Factory::getInstance()->getActivityView()->getDetailviewTemplate($activity)): ?>

            <?$this->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts')?>
            <?$this->template($template)?>

        <?php else: ?>

            <?php $visibleAttributes = \CustomerManagementFramework\Factory::getInstance()->getActivityView()->getDetailviewData($activity); ?>

            <div class="box box-info">
                <div class="box-header with-border with-form">
                    <h3 class="box-title">
                        <?=$activity->getType()?>
                    </h3>

                    <div>
                        <a href="<?=$this->url(["module" => "CustomerManagementFramework", "controller"=>"activities","action"=>"list","customerId"=>$this->getParam("customerId")],null,true)?>" class="btn btn-default btn-xs">&laquo; back</a>
                    </div>
                </div>

                <div class="box-body">
                    <div class="row">
                        <?php

                        $chunkSize = ceil(sizeof($visibleAttributes) / 2);
                        $chunkSize = $chunkSize > 0 ? $chunkSize : 1;

                        foreach (array_chunk($visibleAttributes, $chunkSize, true) as $attributes): ?>

                            <div class="col col-sm-6">
                                <table class="table table-striped">

                                    <?php foreach ($attributes as $key => $value): ?>
                                        <tr>
                                            <th width="200"><?= $key ?></th>
                                            <td>
                                                <?= $value ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                </table>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </section>

<?php endif; ?>
