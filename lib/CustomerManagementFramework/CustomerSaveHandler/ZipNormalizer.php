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
class ZipNormalizer implements CustomerSaveHandlerInterface
{
    private $config;

    private $countryTransformers;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;

        $this->countryTransformers = $config->countryTransformers ? $config->countryTransformers->toArray() : [
            'AT' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\At',
            'DE' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\De',
            'NL' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Nl',
            'DK' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Dk',
            'BE' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Be',
            'RU' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Ru',
            'CH' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Ch',
            'SE' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Se',
            'GB' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\Gb',
        ];

        $this->logger = $logger;
    }


    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function process(CustomerInterface $customer)
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