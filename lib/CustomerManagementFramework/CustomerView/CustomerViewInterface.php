<?php

namespace CustomerManagementFramework\CustomerView;

use CustomerManagementFramework\Model\CustomerInterface;

interface CustomerViewInterface
{
    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getOverviewTemplate(CustomerInterface $customer);

    /**
     * Determines if customer has a detail view or if pimcore object should be openend directly
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function hasDetailView(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    public function getDetailviewTemplate(CustomerInterface $customer);

    /**
     * @param string $value
     * @return string
     */
    public function translate($value);
}
