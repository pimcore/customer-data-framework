<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 30.11.2016
 * Time: 09:56
 */

class CustomerManagementFramework_HelperController extends \Pimcore\Controller\Action\Admin
{

    /**
     * get list of customer fields for action trigger rules
     */
    public function customerFieldListAction()
    {
        $class = \Pimcore\Model\Object\ClassDefinition::getByName(\CustomerManagementFramework\Plugin::getConfig()->General->CustomerPimcoreClass);

        $result = [];

        foreach($class->getFieldDefinitions() as $fieldDefinition) {
            $class = get_class($fieldDefinition);

            if(in_array($class, [
                \Pimcore\Model\Object\ClassDefinition\Data\Checkbox::class,
                \Pimcore\Model\Object\ClassDefinition\Data\Input::class,
                \Pimcore\Model\Object\ClassDefinition\Data\Select::class,
                \Pimcore\Model\Object\ClassDefinition\Data\Numeric::class,
                \Pimcore\Model\Object\ClassDefinition\Data\Textarea::class,
                \Pimcore\Model\Object\ClassDefinition\Data\Slider::class
            ])) {
                $result[] = [$fieldDefinition->getName(), $fieldDefinition->getTitle() ? : $fieldDefinition->getName()];
            }
        }

        @usort($result, function($a, $b){
            return strcmp(strtolower($a[1]), strtolower($b[1]));
        });

        $this->_helper->json($result);
    }
}