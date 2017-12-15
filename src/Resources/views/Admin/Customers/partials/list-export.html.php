<?php
/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;

$exporterConfigs = Pimcore::getContainer()
    ->get('cmf.customer_exporter_manager')
    ->getExporterConfig();

if (count($exporterConfigs) === 0) {
    return;
}
?>

<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 text-right">
                <?php foreach ($exporterConfigs as $exporter => $exporterConfig): ?>

                    <?php
                    $exportParams = array_merge([
                        'exporter' => $exporter,
                    ], $this->clearUrlParams ?: []);

                    $exportUrl = $this->formQueryString($this->request, $this->url('customermanagementframework_admin_customers_export', $exportParams));
                    ?>

                    <a href="#" data-href="<?= $exportUrl ?>" class="btn btn-default js-customer-export">
                        <i class="<?= isset($exporterConfig['icon']) ? $exporterConfig['icon'] : 'fa fa-download' ?>"></i>
                        <?= $cv->translate('cmf_filters_export') ?>
                        <span class="label label-info"><?= $exporterConfig['name'] ?></span>
                    </a>

                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exportModalLabel"><i class="fa fa-clock-o"></i> <?=$this->customerView->translate('cmf_filters_export_generating')?></h4>
            </div>
            <div class="modal-body center-block">
                <span class="js-progress-label"></span>
                <div class="progress">
                    <div class="progress-bar bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">

                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
