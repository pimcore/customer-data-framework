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

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;

/**
 * normalizes the zip field of a given customer according to several country zip formats
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class NormalizeZip extends AbstractCustomerSaveHandler
{
    private $countryTransformers;

    use LoggerAware;

    public function __construct(array $countryTransformers = [])
    {
        $this->countryTransformers = sizeof($countryTransformers) ? $countryTransformers : [
            'AT' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\At',
            'DE' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\De',
            'NL' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Nl',
            'DK' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Dk',
            'BE' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Be',
            'RU' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Ru',
            'CH' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Ch',
            'SE' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Se',
            'GB' => 'CustomerManagementFrameworkBundle\DataTransformer\Zip\Gb',
        ];
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
        $countryCode = $customer->getCountryCode();

        if (!empty($this->countryTransformers[$countryCode])) {
            $transformer = Factory::getInstance()->createObject(
                $this->countryTransformers[$countryCode],
                DataTransformerInterface::class
            );

            $customer->setZip($transformer->transform($customer->getZip()));
        } else {
            $this->getLogger()->debug(sprintf('no zip transformer for country code %s defined', $countryCode));
        }
    }
}
