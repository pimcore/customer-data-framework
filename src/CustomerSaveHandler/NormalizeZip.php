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
use Psr\Log\LoggerInterface;

/**
 * normalizes the zip field of a given customer according to several country zip formats
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class NormalizeZip extends AbstractCustomerSaveHandler
{
    private $countryTransformers;

    public function __construct($config, LoggerInterface $logger)
    {
        parent::__construct($config, $logger);

        $this->countryTransformers = $config->countryTransformers ? $config->countryTransformers->toArray() : [
            'AT' => 'CustomerManagementFramework\DataTransformer\Zip\At',
            'DE' => 'CustomerManagementFramework\DataTransformer\Zip\De',
            'NL' => 'CustomerManagementFramework\DataTransformer\Zip\Nl',
            'DK' => 'CustomerManagementFramework\DataTransformer\Zip\Dk',
            'BE' => 'CustomerManagementFramework\DataTransformer\Zip\Be',
            'RU' => 'CustomerManagementFramework\DataTransformer\Zip\Ru',
            'CH' => 'CustomerManagementFramework\DataTransformer\Zip\Ch',
            'SE' => 'CustomerManagementFramework\DataTransformer\Zip\Se',
            'GB' => 'CustomerManagementFramework\DataTransformer\Zip\Gb',
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
            $this->logger->debug(sprintf('no zip transformer for country code %s defined', $countryCode));
        }
    }
}
