<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\ActivityView;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use Pimcore\Db;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\ClassDefinition\Data;
use Pimcore\Translate\Admin;

class DefaultActivityView implements ActivityViewInterface {

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
                return $implementationClass::cmfGetDetailviewData($activityEntry);
            }
        }

        return false;
    }

    public function formatValueByFieldDefinition(Data $fd, $value) {

        if($fd instanceof Data\Checkbox) {
            return $this->formatCheckboxValue($value);
        }

        if($fd instanceof Data\Datetime) {
            return $this->formatDatetimeValue($value);
        }

        return $value;
    }

    public function getLabelByFieldDefinition(Data $fd) {

        $ta = new Admin(\Zend_Registry::get("Zend_Locale"));
        return $ta->translate($fd->getTitle());
    }

    public function formatAttributes($implementationClass, array $attributes, array $visibleKeys = []) {

        $class = false;
        if($implementationClass) {
            if(method_exists($implementationClass, 'classId')) {
                $class = ClassDefinition::getById($implementationClass::classId());
            }
        }

        if(sizeof($visibleKeys)) {
            $visibleAttributes = [];
            foreach($visibleKeys as $column) {
                $visibleAttributes[$column] = $attributes[$column];
            }
            $attributes = $visibleAttributes;
        }

        $result = [];

        foreach($attributes as $key => $value) {
            if(!is_scalar($value)) {
                unset($attributes[$key]);
                continue;
            }

            if($class && $fd = $class->getFieldDefinition($key)) {
                $result[$this->getLabelByFieldDefinition($fd)] = $this->formatValueByFieldDefinition($fd, $value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    protected function formatCheckboxValue($value) {
        if($value) {
            return '<i class="glyphicon glyphicon-check"></i>';
        }

        return '<i class="glyphicon glyphicon-uncheck"></i>';
    }

    protected function formatDatetimeValue($value) {
        $date = Carbon::parse($value);

        return $date->formatLocalized("%x %X");
    }
}