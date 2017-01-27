<?php
/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;

$exporterConfigs = \CustomerManagementFramework\Factory::getInstance()
    ->getCustomerListExporterManager()
    ->getConfig();

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
                        'controller' => 'customers',
                        'action'     => 'export',
                        'exporter'   => $exporter,
                    ], $this->clearUrlParams ?: []);

                    $exportUrl = $this->formQueryString($this->request, $this->url($exportParams));
                    ?>

                    <a href="<?= $exportUrl ?>" class="btn btn-default">
                        <i class="<?= isset($exporterConfig->icon) ? $exporterConfig->icon : 'fa fa-download' ?>"></i>
                        <?= $cv->translate('Export') ?>
                        <span class="label label-info"><?= $exporterConfig->name ?></span>
                    </a>

                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
