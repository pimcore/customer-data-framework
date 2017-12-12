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

namespace CustomerManagementFrameworkBundle\CustomerView;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ObjectWrapper;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;

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
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials:list-row.html.php';
    }

    public function getFilterWrapperTemplate()
    {
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:filter-wrapper.html.php';
    }

    public function getFieldsFilterTemplate()
    {
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:fields.html.php';
    }

    public function getSegmentsFilterTemplate()
    {
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials/list-filter:segments.html.php';
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
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials:detail.html.php';
    }

    /**
     * @param CustomerInterface|ElementInterface|Concrete $customer
     *
     * @return array
     */
    public function getDetailviewData(CustomerInterface $customer)
    {
        $definition = $customer->getClass();

        $result = [];
        $vf = $this->viewFormatter;

        foreach ($definition->getFieldDefinitions() as $fd) {
            if ($fd->getInvisible()) {
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
     * @param $object
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
