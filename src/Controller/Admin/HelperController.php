<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 30.11.2016
 * Time: 09:56
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/helper")
 */
class HelperController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{

    /**
     * get list of customer fields for action trigger rules
     *
     * @param Request $request
     * @Route("/customer-field-list")
     */
    public function customerFieldListAction(Request $request)
    {
        $class = \Pimcore\Model\Object\ClassDefinition::getByName(Config::getConfig()->General->CustomerPimcoreClass);

        $result = [];

        foreach ($class->getFieldDefinitions() as $fieldDefinition) {
            $class = get_class($fieldDefinition);

            if (in_array(
                $class,
                [
                    \Pimcore\Model\Object\ClassDefinition\Data\Checkbox::class,
                    \Pimcore\Model\Object\ClassDefinition\Data\Input::class,
                    \Pimcore\Model\Object\ClassDefinition\Data\Select::class,
                    \Pimcore\Model\Object\ClassDefinition\Data\Numeric::class,
                    \Pimcore\Model\Object\ClassDefinition\Data\Textarea::class,
                    \Pimcore\Model\Object\ClassDefinition\Data\Slider::class,
                ]
            )) {
                $result[] = [$fieldDefinition->getName(), $fieldDefinition->getTitle() ?: $fieldDefinition->getName()];
            }
        }

        @usort(
            $result,
            function ($a, $b) {
                return strcmp(strtolower($a[1]), strtolower($b[1]));
            }
        );

        return $this->json($result);
    }

    /**
     * get list of available activity types
     *
     * @param Request $request
     * @Route("/activity-types")
     */
    public function activityTypesAction(Request $request)
    {
        $types = \Pimcore::getContainer()->get('cmf.activity_store')->getAvailableActivityTypes();

        $result = [];
        foreach ($types as $type) {
            $result[] = [$type];
        }

        return $this->json($result);
    }
}