<?php

namespace CustomerManagementFramework\CustomerView;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\View\Formatter\ViewFormatterInterface;

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
        return false;
    }

    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getDetailviewTemplate(CustomerInterface $customer)
    {
        return null;
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
