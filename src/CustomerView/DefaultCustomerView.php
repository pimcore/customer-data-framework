<?php

namespace CustomerManagementFrameworkBundle\CustomerView;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ObjectWrapper;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;
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
     * @return ViewFormatterInterface
     */
    public function getViewFormatter()
    {
        return $this->viewFormatter;
    }

    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getOverviewTemplate(CustomerInterface $customer)
    {
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials:list-row.html.php';
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
        return 'PimcoreCustomerManagementFrameworkBundle:Admin/Customers/partials:detail.html.php';
    }

    /**
     * @param CustomerInterface|ElementInterface|Concrete $customer
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
