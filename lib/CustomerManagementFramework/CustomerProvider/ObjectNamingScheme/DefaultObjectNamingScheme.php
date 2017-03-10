<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-10
 * Time: 16:22
 */

namespace CustomerManagementFramework\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFramework\Helper\Objects;
use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Model\Object\Service;

class DefaultObjectNamingScheme implements ObjectNamingSchemeInterface
{
    /**
     * @param CustomerInterface $customer
     * @param string $parentPath
     * @param string $namingScheme
     * @return void
     */
    public function apply(CustomerInterface $customer, $parentPath, $namingScheme) {

        if($namingScheme) {
            $namingScheme = $this->extractNamingScheme($customer, $namingScheme);

            $key = $namingScheme[sizeof($namingScheme) - 1];
            unset($namingScheme[sizeof($namingScheme) - 1]);

            $parentPath .= '/' . implode('/',$namingScheme);
            $customer->setKey($key);
        }

        $parentPath = $this->correctPath($parentPath);

        $customer->setParent(Service::createFolderByPath($parentPath));

        if(!$customer->getKey()) {
            $customer->setKey(uniqid());
        }
        Objects::checkObjectKey($customer);
    }

    private function correctPath($path)
    {
        return str_replace('//', '/', $path);
    }

    /**
     * @param CustomerInterface $customer
     * @param $namingScheme
     * @return array
     */
    private function extractNamingScheme(CustomerInterface $customer, $namingScheme)
    {
        $namingScheme = explode('/', $namingScheme);
        foreach($namingScheme as $i => $namingSchemeItem) {
            preg_match('/{([a-zA-Z0-9]*)}/', $namingSchemeItem, $matchedPlaceholder);

            if(sizeof($matchedPlaceholder)) {
                $placeholder = $matchedPlaceholder[0];
                $field = $matchedPlaceholder[1];

                $getter = 'get' . ucfirst($field);
                if(method_exists($customer, $getter)) {
                    $value = (string) $customer->$getter();
                    $value = $value ? : '--';
                    $namingScheme[$i] = Objects::getValidKey(str_replace($placeholder, $value, $namingSchemeItem));
                }
            }
        }

        return $namingScheme;
    }
}