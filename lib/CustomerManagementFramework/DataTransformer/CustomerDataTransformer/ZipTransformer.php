<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFramework\DataTransformer\CustomerDataTransformer;

use CustomerManagementFramework\DataTransformer\AttributeDataTransformer\AttributeDataTransformerInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;

class ZipTransformer implements CustomerDataTransformerInterface
{
    private $config;
    private $countryTransformers;

    public function __construct($config)
    {
        $this->config = $config;

        $this->countryTransformers = [
            'A' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\At',
            'AT' => 'CustomerManagementFramework\DataTransformer\AttributeDataTransformer\Zip\At'
        ];
    }


    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function transform(CustomerInterface $customer)
    {

        $countryCode = $customer->getCountryCode();

        if(!empty($this->countryTransformers[$countryCode])) {
            $transformer = Factory::getInstance()->createObject($this->countryTransformers[$countryCode], AttributeDataTransformerInterface::class);

            $customer->setZip($transformer->transform($customer->getZip()));
        }

    }
}