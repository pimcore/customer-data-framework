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

namespace CustomerManagementFrameworkBundle\CustomerDuplicatesView;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;
use Pimcore\Model\DataObject\ClassDefinition;

class DefaultCustomerDuplicatesView implements CustomerDuplicatesViewInterface
{
    /**
     * @var array
     */
    protected $listFields;

    /**
     * @var ViewFormatterInterface
     */
    protected $viewFormatter;

    /**
     * @param ViewFormatterInterface $viewFormatter
     */
    public function __construct(array $listFields, ViewFormatterInterface $viewFormatter)
    {
        $this->listFields = $listFields;
        $this->viewFormatter = $viewFormatter;
    }

    /**
     * @return ViewFormatterInterface
     */
    public function getViewFormatter()
    {
        return $this->viewFormatter;
    }

    public function getListData(CustomerInterface $customer)
    {
        $class = ClassDefinition::getById($customer::classId());

        $fields = $this->listFields;
        $listData = [];

        foreach ($fields as $field) {
            if (is_array($field)) {
                $labels = [];
                $values = [];
                foreach ($field as $_field) {
                    $labels[] = $this->viewFormatter->getLabelByFieldName($class, $_field);
                    $values[] = $this->getValueFromCustomer($customer, $_field);
                }
                $listData[implode('/', $labels)] = implode(' ', $values);
            } else {
                $listData[$this->viewFormatter->getLabelByFieldName($class, $field)] = $this->getValueFromCustomer(
                    $customer,
                    $field
                );
            }
        }

        return $listData;
    }

    protected function getValueFromCustomer(CustomerInterface $customer, $fieldName, $format = true)
    {
        if ($fieldName == 'id') {
            return $customer->getId();
        }

        $getter = 'get'.ucfirst($fieldName);
        $value = $customer->$getter();

        if ($format) {
            $class = ClassDefinition::getById($customer::classId());
            $fd = $class->getFieldDefinition($fieldName);
            $value = $this->viewFormatter->formatValueByFieldDefinition($fd, $customer->$getter());
        }

        return $value;
    }
}
