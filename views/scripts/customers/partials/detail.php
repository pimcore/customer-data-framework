<?php
/** @var \CustomerManagementFramework\Model\CustomerInterface $customer */
$customer = $this->customer;

/** @var \CustomerManagementFramework\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;
?>

<div class="box box-info">
    <div class="box-header with-border with-form">
        <h3 class="box-title">
            <a href="#" class="js-pimcore-link" data-pimcore-id="<?= $customer->getId() ?>">
                <i class="fa fa-link"></i>
            </a>

            <?= $customer->getFirstname() ?> <?= $customer->getLastname() ?>
        </h3>
    </div>

    <div class="box-body">
        <div class="row">
            <?php
            $detailAttributes = $cv->getDetailviewData($customer);
            ?>

            <?php foreach (array_chunk($detailAttributes, ceil(sizeof($detailAttributes) / 2), true) as $attributes): ?>

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
