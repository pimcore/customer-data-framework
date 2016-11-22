<?php

namespace CustomerManagementFramework\View\Formatter;

use Carbon\Carbon;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use Pimcore\Model\Object\ClassDefinition\Data;
use Pimcore\Translate\Admin;

class DefaultViewFormatter implements ViewFormatterInterface
{
    /**
     * @var \Zend_Translate_Adapter[]
     */
    protected $translate = [];

    /**
     * @param $value
     * @return array|string
     */
    public function translate($value)
    {
        $locale = (string)\Zend_Registry::get("Zend_Locale");
        if (!$ta = $this->translate[$locale]) {
            $ta = new Admin(\Zend_Registry::get("Zend_Locale"));
            $this->translate[$locale] = $ta;
        }

        return $ta->translate($value);
    }

    /**
     * @param Data $fd
     * @return array|string
     */
    public function getLabelByFieldDefinition(Data $fd)
    {
        return $this->translate($fd->getTitle());
    }

    /**
     * @param Data $fd
     * @param $value
     * @return string
     */
    public function formatValueByFieldDefinition(Data $fd, $value)
    {
        if ($fd instanceof Data\Checkbox) {
            return $this->formatCheckboxValue($value);
        }

        if ($fd instanceof Data\Datetime) {
            return $this->formatDatetimeValue($value);
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $val) {
                $result[] = $this->formatValueByFieldDefinition($fd, $val);
            }

            return implode("\n", $result);
        }

        return $this->formatValue($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function formatValue($value)
    {
        if ($value instanceof CustomerSegmentInterface) {
            return $this->formatSegmentValue($value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatCheckboxValue($value)
    {
        if ($value) {
            return '<i class="glyphicon glyphicon-check"></i>';
        }

        return '<i class="glyphicon glyphicon-uncheck"></i>';
    }

    /**
     * @param $value
     * @return string
     */
    protected function formatDatetimeValue($value)
    {
        $date = Carbon::parse($value);

        return $date->formatLocalized("%x %X");
    }

    /**
     * @param CustomerSegmentInterface $segment
     * @return string
     */
    protected function formatSegmentValue(CustomerSegmentInterface $segment)
    {
        return sprintf('<span class="label label-default">%s</span>', $segment->getName());
    }
}
