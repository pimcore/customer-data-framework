<?php

namespace CustomerManagementFrameworkBundle\View\Formatter;

use CustomerManagementFrameworkBundle\Translate\TranslatorInterface;
use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\ClassDefinition\Data;

interface ViewFormatterInterface extends TranslatorInterface
{
    /**
     * @param Data $fd
     * @return string
     */
    public function getLabelByFieldDefinition(Data $fd);

    /**
     * @param ClassDefinition $class
     * @param string $fieldName
     * @return string
     */
    public function getLabelByFieldName(ClassDefinition $class, $fieldName);

    /**
     * @param mixed $value
     * @return string
     */
    public function formatValue($value);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function formatBooleanValue($value);

    /**
     * @param $value
     * @return string
     */
    public function formatDatetimeValue($value);

    /**
     * @param Data $fd
     * @param $value
     * @return string
     */
    public function formatValueByFieldDefinition(Data $fd, $value);

    /**
     * @param string $locale
     * @return void
     */
    public function setLocale($locale);

    /**
     * @return string
     */
    public function getLocale();
}
