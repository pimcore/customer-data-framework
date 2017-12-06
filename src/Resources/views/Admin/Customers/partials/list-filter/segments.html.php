<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 *
 * @var \CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface $customerView
 * @var array $filters
 * @var \CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition $filterDefinition
 * @var \Pimcore\Model\DataObject\CustomerSegmentGroup[] $segmentGroups
 */

$filteredSegmentGroups = [];
foreach ($segmentGroups as $segmentGroup) {
    if(in_array($segmentGroup->getId(), $filters['showSegments'])) $filteredSegmentGroups[] = $segmentGroup;
}
?>

<?php if (isset($segmentGroups)): ?>

    <fieldset>
        <legend>
            <?= $customerView->translate('Segments') ?>
        </legend>


        <?php
        /** @var \Pimcore\Model\DataObject\CustomerSegmentGroup $segmentGroup */
        foreach (array_chunk($filteredSegmentGroups, 2) as $chunk): ?>
            <div class="row">
                <?php foreach ($chunk as $segmentGroup): ?>
                    <div class="col-md-6 col-xs-12">
                        <div class="form-group">
                            <label for="form-filter-segment-<?= $segmentGroup->getId() ?>"><?= $segmentGroup->getName() ?></label>
                            <select
                                    id="form-filter-segment-<?= $segmentGroup->getId() ?>"
                                    name="filter[segments][<?= $segmentGroup->getId() ?>][]"
                                    class="form-control plugin-select2"
                                    multiple="multiple"
                                    data-placeholder="<?= $segmentGroup->getName() ?>"
                                    data-select2-options='<?= json_encode([
                                        'allowClear' => false,
                                        'disabled' => $filterDefinition->isLockedSegment($segmentGroup->getId()),
                                    ]) ?>'>
                                <?php
                                $segments = \Pimcore::getContainer()
                                    ->get('cmf.segment_manager')
                                    ->getSegmentsFromSegmentGroup($segmentGroup);

                                /** @var \CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface|\Pimcore\Model\Element\ElementInterface $segment */
                                foreach ($segments as $segment): ?>

                                    <option value="<?= $segment->getId() ?>" <?= isset($filters['segments'][$segmentGroup->getId()]) && array_search($segment->getId(),
                                        $filters['segments'][$segmentGroup->getId()]) !== false ? 'selected="selected"' : '' ?>>
                                        <?= $segment->getName() ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

    </fieldset>
<?php endif; ?>
