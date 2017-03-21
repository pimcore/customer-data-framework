<?php

namespace CustomerManagementFramework\CustomerDuplicatesView;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Translate\TranslatorInterface;
use CustomerManagementFramework\View\Formatter\ViewFormatterInterface;

interface CustomerDuplicatesViewInterface extends TranslatorInterface
{
    /**
     * @return ViewFormatterInterface
     */
    public function getViewFormatter();

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function getListData(CustomerInterface $customer);

}
