<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\CustomerView;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Translate\TranslatorInterface;
use CustomerManagementFrameworkBundle\View\Formatter\ViewFormatterInterface;

interface CustomerViewInterface extends TranslatorInterface
{
    /**
     * @return ViewFormatterInterface
     */
    public function getViewFormatter();

    /**
     *
     * @return string|null
     */
    public function getOverviewTemplate(CustomerInterface $customer);

    /**
     * @return string
     */
    public function getOverviewWrapperTemplate();

    /**
     * @return string
     */
    public function getFilterWrapperTemplate();

    /**
     * @return string
     */
    public function getFieldsFilterTemplate();

    /**
     * @return string
     */
    public function getSegmentsFilterTemplate();

    /**
     * Determines if customer has a detail view or if pimcore object should be openend directly
     *
     *
     * @return bool
     */
    public function hasDetailView(CustomerInterface $customer);

    /**
     *
     * @return string|null
     */
    public function getDetailviewTemplate(CustomerInterface $customer);

    /**
     *
     * @return array
     */
    public function getDetailviewData(CustomerInterface $customer);
}
