<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
