<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
$this->extend('PimcoreCustomerManagementFrameworkBundle::layout.html.php');

/**
 * @var \CustomerManagementFrameworkBundle\CustomerDuplicatesView\CustomerDuplicatesViewInterface $duplicatesView
 */
$duplicatesView = $this->duplicatesView;

/**
 * @var \CustomerManagementFrameworkBundle\Model\CustomerDuplicates\PotentialDuplicateItemInterface[] $duplicates
 */
$duplicates = $this->paginator;

$this->jsConfig()->add('declineDuplicates', true);
?>
<div class="container">

    <h2><?=$duplicatesView->getViewFormatter()->translate('cmf_duplicates_potential_duplicates')?></h2>

    <div>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist" style="margin-bottom:20px;">
            <li role="presentation"<?php if (!$this->getParam('declined')) {
    ?> class="active"<?php
} ?>><a href="<?=$this->pimcoreUrl(['declined' => 0])?>"><?=$duplicatesView->getViewFormatter()->translate('cmf_duplicates_current')?></a></li>
            <li role="presentation"<?php if ($this->getParam('declined')) {
        ?> class="active"<?php
    } ?>><a href="<?=$this->pimcoreUrl(['declined' => 1])?>"><?=$duplicatesView->getViewFormatter()->translate('cmf_duplicates_declined')?></a></li>
        </ul>

    </div>

    <table class="table">

        <?php foreach ($duplicates as $duplicate) {
        $listData = $duplicatesView->getListData($duplicate->getDuplicateCustomers()[0]); ?>
            <tbody id="customerduplicates_<?=$duplicate->getDuplicateCustomers()[0]->getId()?>_<?=$duplicate->getDuplicateCustomers()[1]->getId()?>" class="js-duplicates-item duplicates-item" >
                <tr>
                    <td colspan="99">

                        <a class="btn btn-primary btn-xs pull-right" onClick="new window.top.pimcore.plugin.objectmerger.panel(<?=$duplicate->getDuplicateCustomers()[0]->getId()?>, <?=$duplicate->getDuplicateCustomers()[1]->getId()?>); "><?=$duplicatesView->getViewFormatter()->translate('cmf_duplicates_declined')?></a>
                        <?php if (!$this->getParam('declined')) {
            ?>
                            <a class="btn btn-danger btn-xs pull-right js-decline-duplicate" data-id="<?=$duplicate->getId()?>" style="margin-right: 5px;"><?=$duplicatesView->getViewFormatter()->translate('cmf_duplicates_decline_duplicate')?></a>
                        <?php
        } ?>

                        <?php if (PIMCORE_DEBUG) {
            $fieldCombinations = $duplicate->getFieldCombinations();
            foreach ($fieldCombinations as $key => $combination) {
                $fieldCombinations[$key] = implode(', ', $combination);
            } ?>

                            <a class="btn btn-default btn-xs pull-right" style="margin-right: 5px;" data-toggle="tooltip" data-placement="left" data-html="true" title="<div style='text-align:left;'><strong>matched field combinations (duplicate ID <?= $duplicate->getId() ?>):</strong><br/><br/><?= implode('<br>', $fieldCombinations) ?></div>">info (debug)</a>
                        <?php
        } ?>

                    </td>
                </tr>

                <tr>
                    <?php foreach ($listData as $label => $value) {
            ?>
                        <th><?=$label?></th>
                    <?php
        } ?>
                </tr>
                    <?php

                    foreach ($duplicate->getDuplicateCustomers() as $customer) {
                        $listData = $duplicatesView->getListData($customer); ?>
                        <tr>
                            <?php foreach ($listData as $label => $value) {
                            ?>
                                <td><?=$value?></td>
                            <?php
                        } ?>

                        </tr>
                    <?php
                    } ?>

            </tbody>
            <?php
    }?>

    </table>

    <?php if($this->paginator->getPages()->pageCount > 1): ?>
        <div class="text-center">
            <?php if ($paginator->getPages()->pageCount > 1): ?>
                <?= $this->render(
                    "PimcoreCustomerManagementFrameworkBundle:Admin/Partial/Pagination:default.html.php",
                    get_object_vars($paginator->getPages("Sliding"))
                ); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
