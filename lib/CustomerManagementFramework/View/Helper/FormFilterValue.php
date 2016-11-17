<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterValue extends AbstractFormValue
{
    /**
     * Get filter value
     *
     * @param string $name
     * @param string|array|null $property
     * @return string
     */
    public function formFilterValue($name, $property = 'filters')
    {
        $base = $this->getPropertyBase($property);
        if (null !== $base && $this->valueExists($name, $property, $base)) {
            $value = $this->getValue($name, $base);
            $value = trim($value);
            $value = $this->view->escape($value);

            return $value;
        }

        return '';
    }
}
