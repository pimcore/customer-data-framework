<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterSelectedState extends AbstractFormState
{
    /**
     * Check if form select value is selected
     *
     * @param $name
     * @param $value
     * @param bool $multiple
     * @return string
     */
    public function formFilterSelectedState($name, $value, $multiple = false)
    {
        return $this->getStateValue('selected', $name, $value, $multiple, 'filters');
    }
}
