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
 * @var bool $hideAdvancedFilterSettings
 */

$filteredSegmentGroups = [];
foreach ($segmentGroups as $segmentGroup) {
    if (in_array($segmentGroup->getId(), $filters['showSegments'])) {
        $filteredSegmentGroups[] = $segmentGroup;
    }
}
?>

<?php if (isset($segmentGroups)): ?>

    <fieldset>
        <legend>
            <div class="row">
                <div class="col-md-2">
                    <?= $customerView->translate('cmf_filters_segments') ?>
                </div>
                <?php if (!$hideAdvancedFilterSettings && !$filterDefinition->isReadOnly()) : ?>
                    <div class="col-md-10 text-right">
                        <a type="button" class="btn btn-sm" data-toggle="modal"
                           data-target="#show-segments-modal"><?= $customerView->translate('cmf_filters_segments_edit') ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </legend>

        <?php if (!$hideAdvancedFilterSettings): ?>
            <div id="show-segments-modal" class="modal fade text-left" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;
                            </button>
                            <h4 class="modal-title"><?= $customerView->translate('cmf_filters_segments_headline') ?></h4>
                        </div>
                        <div class="modal-body">
                            <?php foreach ($segmentGroups as $segmentGroup): ?>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="checkbox plugin-icheck">
                                            <label>
                                                <input name="filter[showSegments][]"
                                                       value="<?= $segmentGroup->getId() ?>"
                                                       type="checkbox"
                                                    <?= (in_array($segmentGroup->getId(),
                                                        $filters['showSegments'])) ? ' checked="checked"' : '' ?>><?= $segmentGroup->getName() ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="modal-footer">
                            <input type="button" class="btn btn-default"
                                   value="<?= $customerView->translate('cmf_filters_cancel'); ?>"
                                   data-dismiss="modal"/>
                            <input type="submit" class="btn btn-primary"
                                   value="<?= $customerView->translate('cmf_filters_segments_confirm'); ?>"
                                   name="apply-segment-selection"/>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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
                                /** @noinspection MissingService */
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
