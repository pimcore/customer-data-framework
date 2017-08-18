<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerDuplicatesView;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Translate\TranslatorInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;

interface CustomerDuplicatesViewInterface extends TranslatorInterface
{
    /**
     * @return ViewFormatterInterface
     */
    public function getViewFormatter();

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getListData(CustomerInterface $customer);
}
