<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\CustomerSaveHandler;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
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

        if(!empty($this->countryTransformers[$countryCode])) {
            $transformer = Factory::getInstance()->createObject($this->countryTransformers[$countryCode], DataTransformerInterface::class);

            $customer->setZip($transformer->transform($customer->getZip()));
        } else {
            $this->logger->debug(sprintf("no zip transformer for country code %s defined", $countryCode));
        }

    }
}