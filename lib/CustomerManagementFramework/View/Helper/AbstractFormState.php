<?php

namespace CustomerManagementFramework\View\Helper;

abstract class AbstractFormState extends \Zend_View_Helper_Abstract
{
    /**
     * @param $name
     * @param $value
     * @param bool $multiple
     * @param string $property
     * @return string
     */
    public function resolveState($name, $value, $multiple = false, $property = null)
    {
        $state = false;

        $base = $this->view;
        if (null !== $property) {
            if (!isset($this->view->{$property})) {
                return $state;
            }

            $base = $this->view->{$property};
        }

        if (array_key_exists($name, $base)) {
            $filterValue = ($base instanceof \Zend_View) ? $base->{$name} : $base[$name];

            if ($multiple) {
                if (is_array($filterValue)) {
                    if (in_array($value, $filterValue)) {
                        $state = true;
                    }
                }
            } else {
                $state = ($value == $filterValue);
            }
        }

        return $state;
    }

    /**
     * Check if filter has state and return stateValue
     *
     * @param $stateValue
     * @param $name
     * @param $value
     * @param bool $multiple
     * @param string $property
     * @return string
     */
    protected function getStateValue($stateValue, $name, $value, $multiple = false, $property = 'filters')
    {
        if ($this->resolveState($name, $value, $multiple, $property)) {
            return $stateValue;
        }

        return '';
    }
}
