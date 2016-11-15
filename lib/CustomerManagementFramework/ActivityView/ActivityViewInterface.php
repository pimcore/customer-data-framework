<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 14.11.2016
 * Time: 16:02
 */

namespace CustomerManagementFramework\ActivityView;

use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use Pimcore\Model\Object\ClassDefinition\Data;

interface ActivityViewInterface {

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return array
     */
    public function getOverviewAdditionalData(ActivityStoreEntryInterface $activityEntry);

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return array
     */
    public function getDetailviewData(ActivityStoreEntryInterface $activityEntry);

    /**
     * @param ActivityStoreEntryInterface $activityEntry
     *
     * @return string|int
     */
    public function getDetailviewTemplate(ActivityStoreEntryInterface $activityEntry);

    /**
     * @param Data $fd
     * @param $value
     *
     * @return mixed
     */
    public function formatAttributes($implementationClass, array $attributes, array $visibleKeys = []);

    /**
     * @param Data $fd
     * @param $value
     *
     * @return mixed
     */
    public function formatValueByFieldDefinition(Data $fd, $value);

    /**
     * @param Data $fd
     *
     * @return mixed
     */
    public function getLabelByFieldDefinition(Data $fd);

    /**
     * @param string $value
     *
     * @return string
     */
    public function translate($value);
}