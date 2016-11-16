<?php
/** @var Zend_Paginator|\Pimcore\Model\Object\Customer[] $paginator */
$paginator = $this->paginator;
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
                <table class="table table-bordered table-striped">
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
                    foreach ($paginator as $customer): ?>

                        <?php
                        $userDetailUrl = $this->url([
                            'module'     => 'CustomerDataFramework',
                            'controller' => 'customers',
                            'action'     => 'detail',
                            'id'         => $customer->getId()
                        ], null, true);
                        ?>

                        <tr>
                            <td class="reference-id-column table-id-column">
                                <a href="<?= $userDetailUrl ?>"><?= $customer->getId() ?></a>
                            </td>
                            <td class="icon-column icon-column--center">
                                <?= $this->partial('customers/partials/active-state.php', [
                                    'customer' => $customer,
                                    'language' => $this->language
                                ]); ?>
                            </td>
                            <td>
                                <?= $this->escape($customer->getFirstname()) ?>
                                <?= $this->escape($customer->getName()) ?>
                                <?= $this->escape($customer->getSurname()) ?>
                            </td>
                            <td>
                                <?= $this->escape($customer->getEmail()) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->

            <?= $this->template('partials/table/pagination-footer.php') ?>
        </div>

    <?php endif; ?>

</section>
