<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
$this->extend('PimcoreCustomerManagementFrameworkBundle::layout.html.php');

/**
 * @var \Zend\Paginator\Paginator $paginator
 */
$paginator = $this->activities;

/**
 * @var \CustomerManagementFrameworkBundle\ActivityView\ActivityViewInterface $av ;
 */
$av = $this->activityView;

$contentBoxClasses = [];
if(sizeof($paginator)) {
    $contentBoxClasses = ['no-padding', 'table-responsive'];
}

$this->jsConfig()->add('formAutoSubmit', true);
?>

<section class="content">
    <div class="box box-info">
        <div class="box-header with-border with-form">
            <h3 class="box-title">
                <?= $av->translate('cmf_activities_activities') ?>
            </h3>

            <div class="box-tools pull-right">
                <form>
                    <div class="form-group">
                        <select name="type" class="form-control js-form-auto-submit">
                            <option value=""><?= $av->translate('cmf_activities_all_activity_types') ?></option>
                            <?php foreach($this->types as $type): ?>
                                <option value="<?= $type ?>"<?php if($this->type == $type) {
                                    ?> selected="selected"<?php
                                } ?>><?= $av->translate($type) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <input type="hidden" name="customerId" value="<?= $this->getParam('customerId') ?>"/>
                    </div>
                </form>
            </div>
        </div>

        <div class="box-body <?= implode(' ', $contentBoxClasses) ?>">
            <?php if(sizeof($paginator)): ?>
                <table class="table table-striped table-bordered table-hover">
                    <tr>
                        <th><?= $av->translate('cmf_activities_activity') ?></th>
                        <th><?= $av->translate('cmf_activities_activity_date') ?></th>
                        <th><?= $av->translate('cmf_activities_activity_details') ?></th>
                    </tr>

                    <?php foreach($paginator as $activity) {
                        /**
                         * @var \CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface $activity
                         */ ?>

                        <tr>
                            <td width="300">
                                <?= $av->translate($activity->getType()) ?>
                                <a href="<?= $this->url('customermanagementframework_admin_activities_detail',
                                    ['activityId' => $activity->getId(), 'customerId' => $this->customer->getId()]) ?>"
                                   class="btn btn-default btn-xs"><?= $av->translate('cmf_activities_details') ?></a>
                            </td>
                            <td width="160"><?= $activity->getActivityDate()->formatLocalized('%x %X') ?></td>
                            <td>
                                <?php if($data = $av->getOverviewAdditionalData($activity)) {
                                    ?>
                                    <table class="overview-data-table">
                                        <tr>
                                            <?php foreach($data as $key => $value) {
                                                ?>
                                                <th><?= $key ?></th>
                                                <?php
                                            } ?>
                                        </tr>
                                        <tr>
                                            <?php foreach($data as $key => $value) {
                                                ?>
                                                <td><?= $value ?></td>
                                                <?php
                                            } ?>
                                        </tr>
                                    </table>
                                    <?php
                                } ?>
                            </td>
                        </tr>
                        <?php
                    } ?>

                </table>

                <?php if($paginator->getPages()->pageCount > 1): ?>
                    <div class="text-center">
                        <?php if($paginator->getPages()->pageCount > 1): ?>
                            <?= $this->render(
                                'PimcoreCustomerManagementFrameworkBundle:Admin/Partial/Pagination:default.html.php',
                                get_object_vars($paginator->getPages('Sliding'))
                            ); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>

                <div class="callout callout-warning">
                    <p><?= $av->translate('cmf_activities_no_activity_found') ?></p>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>
