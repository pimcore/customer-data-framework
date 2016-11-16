<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterCheckedState extends AbstractFormState
{
    /**
     * Check if form checkbox/radio value is checked
     *
     * @param $name
     * @param $value
     * @param bool $multiple
     * @return string
     */
    public function formFilterCheckedState($name, $value, $multiple = false)
    {
        return $this->getStateValue('checked', $name, $value, $multiple, 'filters');
    }
}
