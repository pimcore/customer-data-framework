<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
$this->headScript()->appendFile('/bundles/pimcorecustomermanagementframework/vendor/clipboardjs/clipboard.min.js');
$this->headScript()->appendFile('/bundles/pimcorecustomermanagementframework/js/LinkActivityDefinition/preview.js');

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
                        <?php if ($activityDefinition): ?>
                            <?php $link = $app->getContainer()->get('cmf.link-activity-definition.linkgenerator')->generate($activityDefinition); ?>
                            <?php if ($link): ?>
                                <a href="#" data-clipboard-target="#js-copy-to-clipboard-container" style="margin-right: 10px;" class="btn btn-sm btn-default pull-left js-copy-to-clipboard" title="copy to clipboard">
                                    <i class="fa fa-clipboard"></i>
                                </a>

                                <div id="js-copy-to-clipboard-container" style="padding-top: 5px;" href="<?=$link?>"><?=$link?></div>

                            <?php else: ?>
                                -
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>