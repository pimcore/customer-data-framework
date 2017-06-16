<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFrameworkBundle\ActivityView;

use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;
use Pimcore\Model\Object\ClassDefinition;

class DefaultActivityView implements ActivityViewInterface {

    /**
     * @var ViewFormatterInterface
     */
    protected $viewFormatter;

    /**
     * @param ViewFormatterInterface $viewFormatter
     */
    public function __construct(ViewFormatterInterface $viewFormatter)
    {
        $this->viewFormatter = $viewFormatter;
    }

    public function getOverviewAdditionalData(ActivityStoreEntryInterface $activityEntry) {

        $implementationClass = $activityEntry->getImplementationClass();
        if(class_exists($implementationClass)) {
            if(method_exists($implementationClass, 'cmfGetOverviewData')) {
                return $implementationClass::cmfgetOverviewData($activityEntry);
            }
        }

        return false;
    }

    public function getDetailviewData(ActivityStoreEntryInterface $activityEntry) {

        $implementationClass = $activityEntry->getImplementationClass();
        if(class_exists($implementationClass)) {
            if(method_exists($implementationClass, 'cmfGetDetailviewData')) {
                $data = $implementationClass::cmfGetDetailviewData($activityEntry);
                return $data ? : [];
            }
        }

        return [];
    }

    public function getDetailviewTemplate(ActivityStoreEntryInterface $activityEntry)
    {
        $implementationClass = $activityEntry->getImplementationClass();
        if(class_exists($implementationClass)) {
            if(method_exists($implementationClass, 'cmfGetDetailviewTemplate')) {
                return $implementationClass::cmfGetDetailviewTemplate($activityEntry);
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function translate($messageId, $parameters = [])
    {
        return $this->viewFormatter->translate($messageId, $parameters);
    }

    public function formatAttributes($implementationClass, array $attributes, array $visibleKeys = []) {

        $class = false;
        if($implementationClass) {
            if(method_exists($implementationClass, 'classId')) {
                $class = ClassDefinition::getById($implementationClass::classId());
            }
        }

        $attributes = $this->extractVisibleAttributes($attributes, $visibleKeys);
        if(method_exists($implementationClass, 'cmfGetAttributeDataTypes')) {
            $dataTypes = (array)$implementationClass::cmfGetAttributeDataTypes();
        }
        $dataTypes = is_array($dataTypes) ? $dataTypes : [];

        $result = [];
        $vf     = $this->viewFormatter;

        foreach($attributes as $key => $value) {
            if(!is_scalar($value)) {
                unset($attributes[$key]);
                continue;
            }

            if($class && $fd = $class->getFieldDefinition($key)) {
                $result[$vf->getLabelByFieldDefinition($fd)] = $vf->formatValueByFieldDefinition($fd, $value);
                continue;
            }elseif(isset($dataTypes[$key])) {
                if($dataTypes[$key] == ActivityInterface::DATATYPE_BOOL) {
                    $value = $this->viewFormatter->formatBooleanValue($value);
                }
            }

            $result[$key] = $value;
        }

        return $result;
    }

    private function extractVisibleAttributes(array $attributes, array $visibleKeys) {
        if(sizeof($visibleKeys)) {
            $visibleAttributes = [];
            foreach($visibleKeys as $column) {
                $visibleAttributes[$column] = $attributes[$column];
            }
            return $visibleAttributes;
        }

        return $attributes;
    }
}
