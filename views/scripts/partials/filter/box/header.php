<?php
use CustomerManagementFramework\Listing\Listing;

/** @var Zend_Paginator $paginator */
$paginator = $this->paginator;

// reset page when changing filters
$formActionParams = [
    'page'    => null,
    'perPage' => null
];

if (null !== $paginator && $paginator->getItemCountPerPage() !== Listing::DEFAULT_PAGE_SIZE) {
    $formActionParams['perPage'] = $paginator->getItemCountPerPage();
}

$formAction = $this->url($formActionParams);
?>

<!-- Filters -->
<div class="box box-default box-collapsible-state search-filters" data-identifier="<?= $this->identifier ?>">
    <div class="box-header with-border">
        <h3 class="box-title">
            <a href="#" data-widget="collapse-trigger">
                <i class="fa fa-filter"></i>
                Filters
            </a>
        </h3>
        <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->

    <form role="form" action="<?= $formAction ?>">
        <div class="box-body">

