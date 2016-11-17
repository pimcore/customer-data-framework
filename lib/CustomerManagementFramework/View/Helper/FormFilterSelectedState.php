<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterSelectedState extends AbstractFormState
{
    /**
     * Check if form select value is selected
     *
     * @param string $name
     * @param mixed $value
     * @param bool $multiple
     * @param array|string|null $property
     * @return string
     */
    public function formFilterSelectedState($name, $value, $multiple = false, $property = 'filters')
    {
        return $this->getStateValue('selected', $name, $value, $multiple, $property);
    }
}
