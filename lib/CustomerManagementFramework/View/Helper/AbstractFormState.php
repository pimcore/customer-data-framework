<?php

namespace CustomerManagementFramework\View\Helper;

abstract class AbstractFormState extends AbstractFormValue
{
    /**
     * Resolve form field state from property
     *
     * @param $name
     * @param $value
     * @param bool $multiple
     * @param string $property
     * @return string
     */
    public function resolveState($name, $value, $multiple = false, $property = null)
    {
        $state = false;

        $base = $this->getPropertyBase($property);
        if (null === $base) {
            return $state;
        }

        if ($this->valueExists($name, $property, $base)) {
            $filterValue = $this->getValue($name, $base);

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
