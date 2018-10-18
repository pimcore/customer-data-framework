<?php
/** @var \Zend\Paginator\Paginator|\CustomerManagementFrameworkBundle\Model\CustomerInterface[] $paginator */
$paginator = $this->paginator;

/** @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $cv */
$cv = $this->customerView;

$clearUrlParams = $request->query->all() ?: [];
unset($clearUrlParams['order']);
?>

<table class="table table-striped table-bordered table-hover dataTable">
    <thead>
    <tr>
        <th class="table-id-column">#</th>
        <th width="80px">
            <div class="pos">
                <a class="<?= "sorting". $request->get('order')["o_id"] ?>" href="<?= $this->url('customermanagementframework_admin_customers_list', $this->formOrderParams()->getOrderParams($request, "o_id")) ?>">ID</a>
            </div>
        </th>
        <th class="icon-column icon-column--center"></th>
        <th width="15%">
            <div class="pos">
                <a class="<?= "sorting". $request->get('order')["firstname"] ?>" href="<?= $this->url('customermanagementframework_admin_customers_list', $this->formOrderParams()->getOrderParams($request, "firstname")) ?>"><?= $cv->translate('cmf_filters_customer_firstname') ?></a>
            </div>
        </th>
        <th width="15%">
            <div class="pos">
                <a class="<?= "sorting".  $request->get('order')["lastname"] ?>" href="<?= $this->url('customermanagementframework_admin_customers_list', $this->formOrderParams()->getOrderParams($request, "lastname")) ?>"><?= $cv->translate('cmf_filters_customer_lastname') ?></a>
            </div>
        </th>
        <th width="15%">
            <div class="pos">
                <a class="<?= "sorting".  $request->get('order')["email"] ?>" href="<?= $this->url('customermanagementframework_admin_customers_list', $this->formOrderParams()->getOrderParams($request, "email")) ?>"><?= $cv->translate('cmf_filters_customer_email') ?></a>
            </div>
        </th>
        <th width="100px">
            <div class="pos">
                <a class="<?= "sorting".  $request->get('order')["gender"] ?>" href="<?= $this->url('customermanagementframework_admin_customers_list', $this->formOrderParams()->getOrderParams($request, "gender")) ?>"><?= $cv->translate('cmf_filters_customer_gender') ?></a>
            </div>
        </th>
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