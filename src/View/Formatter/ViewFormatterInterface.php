<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\View\Formatter;

use CustomerManagementFrameworkBundle\Translate\TranslatorInterface;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data;

interface ViewFormatterInterface extends TranslatorInterface
{
    /**
     * @param Data $fd
     *
     * @return string
     */
    public function getLabelByFieldDefinition(Data $fd);

    /**
     * @param ClassDefinition $class
     * @param string $fieldName
     *
     * @return string
     */
    public function getLabelByFieldName(ClassDefinition $class, $fieldName);

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function formatValue($value);

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function formatBooleanValue($value);

    /**
     * @param $value
     *
     * @return string
     */
    public function formatDatetimeValue($value);

    /**
     * @param Data $fd
     * @param $value
     *
     * @return string
     */
    public function formatValueByFieldDefinition(Data $fd, $value);

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale);

    /**
     * @return string
     */
    public function getLocale();
}
