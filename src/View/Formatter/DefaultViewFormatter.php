<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\View\Formatter;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultViewFormatter implements ViewFormatterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    protected $locale;

    /**
     * @param string $messageId
     * @param array $parameters
     *
     * @return string
     */
    public function translate($messageId, $parameters = [])
    {
        if (!is_array($parameters)) {
            if (!empty($parameters)) {
                $parameters = [$parameters];
            } else {
                $parameters = [];
            }
        }

        return $this->translator->trans($messageId, $parameters, 'admin');
    }

    /**
     * @param Data $fd
     *
     * @return array|string
     */
    public function getLabelByFieldDefinition(Data $fd)
    {
        return $this->translate($fd->getTitle());
    }

    public function getLabelByFieldName(ClassDefinition $class, $fieldName)
    {
        if ($fieldName == 'id') {
            return 'ID';
        }

        $fd = $class->getFieldDefinition($fieldName);

        return $this->getLabelByFieldDefinition($fd);
    }

    /**
     * @param Data $fd
     * @param $value
     *
     * @return string
     */
    public function formatValueByFieldDefinition(Data $fd, $value)
    {
        if ($fd instanceof Data\Checkbox) {
            return $this->formatBooleanValue($value);
        }

        if ($fd instanceof Data\Datetime) {
            return $this->formatDatetimeValue($value);
        }

        if ($fd instanceof Data\Date) {
            return $this->formatDatetimeValue($value, true);
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
     *
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
     *
     * @return string
     */
    public function formatBooleanValue($value)
    {
        if ($value) {
            return '<i class="glyphicon glyphicon-check"></i>';
        }

        return '<i class="glyphicon glyphicon-unchecked"></i>';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function formatDatetimeValue($value, $dateOnly = false)
    {
        $this->applyLocale();

        if (is_object($value) && method_exists($value, 'getTimestamp')) {
            $value = date('Y-m-d H:i:s', $value->getTimestamp());
        }

        $date = Carbon::parse($value);

        if ($dateOnly) {
            return $date->formatLocalized('%x');
        }

        return $date->formatLocalized('%x %X');
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param CustomerSegmentInterface $segment
     *
     * @return string
     */
    protected function formatSegmentValue(CustomerSegmentInterface $segment)
    {
        return sprintf('<span class="label label-default">%s</span>', $segment->getName());
    }

    protected function getLanguageFromLocale($locale)
    {
        return explode('_', $locale)[0];
    }

    /**
     * @return string
     */
    protected function applyLocale()
    {
        $locale = $this->getLocale() ?: \Pimcore::getContainer()->get('pimcore.locale')->getLocale();

        $dateLocaleMap = [
            'de' => 'de_AT',
        ];

        setlocale(LC_TIME, isset($dateLocaleMap[$locale]) ? $dateLocaleMap[$locale] : $locale);
        Carbon::setLocale($this->getLanguageFromLocale($locale));

        return $locale;
    }
}
