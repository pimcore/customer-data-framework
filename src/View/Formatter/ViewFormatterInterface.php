<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
     * @param mixed $value
     *
     * @return string
     */
    public function formatDatetimeValue($value);

    /**
     * @param Data $fd
     * @param mixed $value
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
