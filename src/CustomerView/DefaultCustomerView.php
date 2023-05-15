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

namespace CustomerManagementFrameworkBundle\CustomerView;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ObjectWrapper;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;

class DefaultCustomerView implements CustomerViewInterface
{
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

    /**
     * @return ViewFormatterInterface
     */
    public function getViewFormatter()
    {
        return $this->viewFormatter;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return string|null
     */
    public function getOverviewTemplate(CustomerInterface $customer)
    {
        return '@PimcoreCustomerManagementFramework/admin/customers/partials/list_row.html.twig';
    }

    /**
     * @return string
     */
    public function getOverviewWrapperTemplate()
    {
        return '@PimcoreCustomerManagementFramework/admin/customers/partials/list_table.html.twig';
    }

    public function getFilterWrapperTemplate()
    {
        return '@PimcoreCustomerManagementFramework/admin/customers/partials/list_filter/filter_wrapper.html.twig';
    }

    public function getFieldsFilterTemplate()
    {
        return '@PimcoreCustomerManagementFramework/admin/customers/partials/list_filter/fields.html.twig';
    }

    public function getSegmentsFilterTemplate()
    {
        return '@PimcoreCustomerManagementFramework/admin/customers/partials/list_filter/segments.html.twig';
    }

    /**
     * Determines if customer has a detail view or if pimcore object should be openend directly
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function hasDetailView(CustomerInterface $customer)
    {
        return true;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return string|null
     */
    public function getDetailviewTemplate(CustomerInterface $customer)
    {
        return '@PimcoreCustomerManagementFramework/admin/customers/partials/detail.html.twig';
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getDetailviewData(CustomerInterface $customer)
    {
        $definition = $customer->getClass();

        $result = [];
        $vf = $this->viewFormatter;

        foreach ($definition->getFieldDefinitions() as $fd) {
            if ($fd->getInvisible() || $fd->getFieldtype() === 'password') {
                continue;
            }

            $getter = 'get'.ucfirst($fd->getName());
            $value = $vf->formatValueByFieldDefinition($fd, $customer->$getter());

            if (is_object($value)) {
                $value = $this->wrapObject($value);
            }

            $result[$vf->getLabelByFieldDefinition($fd)] = $vf->formatValueByFieldDefinition($fd, $value);
        }

        return $result;
    }

    /**
     * Wrap object in a object implementing a __toString method
     *
     * @param mixed $object
     *
     * @return ObjectWrapper
     */
    protected function wrapObject($object)
    {
        return new ObjectWrapper($object);
    }

    /**
     * @inheritDoc
     */
    public function translate($messageId, $parameters = [])
    {
        return $this->viewFormatter->translate($messageId, $parameters);
    }
}
