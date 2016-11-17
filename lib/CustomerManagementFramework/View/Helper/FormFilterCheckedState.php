<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterCheckedState extends AbstractFormState
{
    /**
     * Check if form checkbox/radio value is checked
     *
     * @param string $name
     * @param mixed $value
     * @param bool $multiple
     * @param array|string|null $property
     * @return string
     */
    public function formFilterCheckedState($name, $value, $multiple = false, $property = 'filters')
    {
        return $this->getStateValue('checked', $name, $value, $multiple, $property);
    }
}
