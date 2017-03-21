<?php

/**
 * @var \CustomerManagementFramework\CustomerDuplicatesView\CustomerDuplicatesViewInterface $duplicatesView
 */
$duplicatesView = $this->duplicatesView;

/**
 * @var \CustomerManagementFramework\CustomerDuplicates\PotentialDuplicateItemInterface[] $duplicates
 */
$duplicates = $this->duplicates;
?>
<div class="container">

    <h2><?=$duplicatesView->getViewFormatter()->translate('Potential customer duplicates')?></h2>

    <table class="table">

        <?php foreach($duplicates as $duplicate) {
            $listData = $duplicatesView->getListData($duplicate->getDuplicateCustomers()[0]);
        ?>
            <tbody id="customerduplicates_<?=$duplicate->getDuplicateCustomers()[0]->getId()?>_<?=$duplicate->getDuplicateCustomers()[1]->getId()?>" class="js-duplicates-item" style="border-bottom: 20px solid #ecf0f5; background-color: #fff;">
                <tr>
                    <td colspan="99">

                        <a class="btn btn-primary btn-xs pull-right" onClick="new window.top.pimcore.plugin.objectmerger.panel(<?=$duplicate->getDuplicateCustomers()[0]->getId()?>, <?=$duplicate->getDuplicateCustomers()[1]->getId()?>); "><?=$duplicatesView->getViewFormatter()->translate('merge')?></a>
                        <a class="btn btn-danger btn-xs pull-right js-decline-duplicate" data-id="<?=$duplicate->getId()?>" style="margin-right: 5px;"><?=$duplicatesView->getViewFormatter()->translate('decline duplicate')?></a>

                        <?php if(PIMCORE_DEBUG) {

                            $fieldCombinations = $duplicate->getFieldCombinations();
                            foreach($fieldCombinations as $key => $combination) {
                                $fieldCombinations[$key] = implode(', ', $combination);
                            }

                            ?>

                            <a class="btn btn-default btn-xs pull-right" style="margin-right: 5px;" data-toggle="tooltip" data-placement="left" data-html="true" title="<div style='text-align:left;'><strong>matched field combinations (duplicate ID <?= $duplicate->getId() ?>):</strong><br/><br/><?= implode('<br>', $fieldCombinations) ?></div>">info (debug)</a>
                        <?php } ?>

                    </td>
                </tr>

                <tr>
                    <?foreach($listData as $label => $value) {?>
                        <th><?=$label?></th>
                    <?}?>
                </tr>
                    <?php

                    foreach($duplicate->getDuplicateCustomers() as $customer) {
                        $listData = $duplicatesView->getListData($customer);
                    ?>
                        <tr>
                            <?php foreach($listData as $label => $value) {?>
                                <td><?=$value?></td>
                            <?php } ?>

                        </tr>
                    <?php } ?>

            </tbody>
            <?php }?>

    </table>

    <?php if($this->paginator->getPages()->pageCount > 1): ?>
        <div class="text-center">
            <?= $this->paginationControl($paginator, 'Sliding', 'includes/pagination/default.php', ['params'=>$this->getAllParams()]); ?>
        </div>
    <?php endif; ?>
</div>