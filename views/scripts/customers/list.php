<?php
/** @var Zend_Paginator|\CustomerManagementFramework\Model\CustomerInterface[] $paginator */
$paginator = $this->paginator;

/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $customerView */
$customerView = $this->customerView;
$cv           = $customerView;
?>

<section class="content">

    <?php if (isset($this->errors) && count($this->errors) > 0): ?>
        <?php foreach ($this->errors as $error): ?>

            <div class="callout callout-danger">
                <?= $error ?>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>

    <?= $this->template('customers/partials/list-filter.php'); ?>

    <?php if ($paginator->getTotalItemCount() === 0): ?>

        <div class="callout callout-warning">
            <p><?= $cv->translate('No results.') ?></p>
        </div>

    <?php else: ?>

        <!-- List -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user"></i>
                    <?= $cv->translate('Customers') ?>
                </h3>
            </div>
            <!-- /.box-header -->

            <div class="box-body no-padding table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="reference-id-column table-id-column">#</th>
                        <th class="icon-column icon-column--center"></th>
                        <th><?= $cv->translate('Name') ?></th>
                        <th><?= $cv->translate('E-Mail') ?></th>
                        <th><?= $cv->translate('Gender') ?></th>
                        <th><?= $cv->translate('Segments') ?></th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php
                    foreach ($paginator as $customer) {
                        echo $this->template($customerView->getOverviewTemplate($customer), [
                            'customer' => $customer
                        ]);
                    }
                    ?>

                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->

            <?= $this->template('partials/table/pagination-footer.php') ?>

            <?= $this->template('customers/partials/list-export.php'); ?>
        </div>

    <?php endif; ?>

</section>
