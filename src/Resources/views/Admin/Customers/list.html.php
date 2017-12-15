<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
$this->extend('PimcoreCustomerManagementFrameworkBundle::layout.html.php');

$this->headScript()->appendFile('/bundles/pimcorecustomermanagementframework/js/CustomerView/frontend.js');

/** @var \Zend\Paginator\Paginator|\CustomerManagementFrameworkBundle\Model\CustomerInterface[] $paginator */
$paginator = $this->paginator;

/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<section class="content">

    <?php if (isset($this->errors) && count($this->errors) > 0): ?>
        <?php foreach ($this->errors as $error): ?>

            <div class="callout callout-danger">
                <?= $error ?>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>

    <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials:list-filter.html.php'); ?>

    <?php if ($paginator->getTotalItemCount() === 0): ?>

        <div class="callout callout-warning">
            <p><?= $cv->translate('cmf_filters_no_results') ?></p>
        </div>

    <?php else: ?>

        <!-- List -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user"></i>
                    <?= $cv->translate('cmf_filters_customers') ?>
                </h3>
            </div>
            <!-- /.box-header -->

            <div class="box-body no-padding table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="table-id-column">#</th>
                        <th class="reference-id-column">ID</th>
                        <th class="icon-column icon-column--center"></th>
                        <th><?= $cv->translate('cmf_filters_customer_name') ?></th>
                        <th><?= $cv->translate('cmf_filters_customer_email') ?></th>
                        <th><?= $cv->translate('cmf_filters_customer_gender') ?></th>
                        <th><?= $cv->translate('cmf_filters_segments') ?></th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php
                    foreach ($paginator as $customer) {
                        echo $this->template($cv->getOverviewTemplate($customer), [
                            'customer' => $customer
                        ]);
                    }
                    ?>

                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
            <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Partial/Table:pagination-footer.html.php') ?>


            <?= $this->template('PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials:list-export.html.php'); ?>
        </div>

    <?php endif; ?>

</section>
