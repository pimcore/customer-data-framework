<?php
/** @var Zend_Paginator|\CustomerManagementFramework\Model\CustomerInterface[] $paginator */
$paginator = $this->paginator;

/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $customerView */
$customerView = $this->customerView;
?>

<section class="content">

    <?= $this->template('customers/partials/list-filter.php'); ?>

    <?php if ($paginator->getTotalItemCount() === 0): ?>

        <div class="callout callout-warning">
            <p>No results.</p>
        </div>

    <?php else: ?>

        <!-- List -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user"></i>
                    Customers
                </h3>
            </div>
            <!-- /.box-header -->

            <div class="box-body no-padding table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="reference-id-column table-id-column">#</th>
                        <th class="icon-column icon-column--center"></th>
                        <th>Name</th>
                        <th>E-Mail</th>
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
