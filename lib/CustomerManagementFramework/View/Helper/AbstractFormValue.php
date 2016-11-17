<?php

namespace CustomerManagementFramework\View\Helper;

abstract class AbstractFormValue extends \Zend_View_Helper_Abstract
{
    /**
     * Get base property to operate on (directly on view or in a variable nested deeper on view)
     *
     * @param array|string|null $property
     * @return array|\Zend_View|null
     */
    protected function getPropertyBase($property = null)
    {
        /** @var \Zend_View|array $base */
        $base = $this->view;

        if (null !== $property) {
            if (!is_array($property)) {
                $property = [$property];
            }

            foreach ($property as $propertyPart) {
                if ($base instanceof \Zend_View) {
                    if (isset($base->{$propertyPart})) {
                        $base = $base->{$propertyPart};
                    } else {
                        return null;
                    }
                } else {
                    if (array_key_exists($propertyPart, $base)) {
                        $base = $base[$propertyPart];
                    } else {
                        return null;
                    }
                }
            }
        }

        return $base;
    }

    /**
     * @param string $name
     * @param array|string|null $property
     * @param array|\Zend_View|null $base
     * @return bool
     */
    protected function valueExists($name, $property = null, $base = null)
    {
        if (null === $base) {
            $base = $this->getPropertyBase($property);
        }

        if ($base instanceof \Zend_View) {
            return isset($base->{$name});
        } else {
            return array_key_exists($name, $base);
        }
    }

    /**
     * @param string $name
     * @param array|\Zend_View $base
     * @return mixed
     */
    protected function getValue($name, $base)
    {
        return ($base instanceof \Zend_View) ? $base->{$name} : $base[$name];
    }
}
