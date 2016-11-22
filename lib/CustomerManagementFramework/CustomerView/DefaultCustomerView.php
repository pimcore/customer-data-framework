<?php

namespace CustomerManagementFramework\CustomerView;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\View\Formatter\ViewFormatterInterface;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\Concrete;

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
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getOverviewTemplate(CustomerInterface $customer)
    {
        return 'customers/partials/list-row.php';
    }

    /**
     * Determines if customer has a detail view or if pimcore object should be openend directly
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function hasDetailView(CustomerInterface $customer)
    {
        return true;
    }

    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getDetailviewTemplate(CustomerInterface $customer)
    {
        return 'customers/partials/detail.php';
    }

    /**
     * @param CustomerInterface|ElementInterface|Concrete $customer
     * @return array
     */
    public function getDetailviewData(CustomerInterface $customer)
    {
        $definition = $customer->getClass();

        $result = [];
        $vf     = $this->viewFormatter;

        foreach ($definition->getFieldDefinitions() as $fd) {
            $getter = 'get' . ucfirst($fd->getName());
            $result[$vf->getLabelByFieldDefinition($fd)] = $vf->formatValueByFieldDefinition($fd, $customer->$getter());
        }

        return $result;
    }

    /**
     * @param string $value
     * @return string
     */
    public function translate($value)
    {
        return $this->viewFormatter->translate($value);
    }
}
