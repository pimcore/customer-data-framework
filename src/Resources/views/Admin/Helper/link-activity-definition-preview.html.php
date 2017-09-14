<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

$this->extend('PimcoreCustomerManagementFrameworkBundle::layout.html.php');

/**
 * @var \Pimcore\Model\DataObject\LinkActivityDefinition $activityDefinition
 */
$activityDefinition = $this->activityDefinition;

?>

<div class="wrapper">
    <div class="content-wrapper">

        <section class="content">
            <div class="container">

                <div class="box box-info">

                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-link"></i>
                            Link
                        </h3>
                    </div>
                    <div class="box-body">
                        <?php if($activityDefinition): ?>
                            <?php $link = $app->getContainer()->get('cmf.link-activity-definition.linkgenerator')->generate($activityDefinition); ?>
                            <?= $link ? : '-' ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>