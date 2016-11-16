<?php

namespace CustomerManagementFramework\View\Helper;

class FormSelectedState extends AbstractFormState
{
    /**
     * Check if form select value is selected
     *
     * @param $name
     * @param $value
     * @param bool $multiple
     * @param string|null $property
     * @return string
     */
    public function formSelectedState($name, $value, $multiple = false, $property = null)
    {
        return $this->getStateValue('selected', $name, $value, $multiple, $property);
    }
}
