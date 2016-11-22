<?php
namespace CustomerManagementFramework\View\Formatter;

use Pimcore\Model\Object\ClassDefinition\Data;

interface ViewFormatterInterface
{
    /**
     * @param $value
     * @return array|string
     */
    public function translate($value);

    /**
     * @param Data $fd
     * @return array|string
     */
    public function getLabelByFieldDefinition(Data $fd);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function formatValue($value);

    /**
     * @param Data $fd
     * @param $value
     * @return string
     */
    public function formatValueByFieldDefinition(Data $fd, $value);
}
