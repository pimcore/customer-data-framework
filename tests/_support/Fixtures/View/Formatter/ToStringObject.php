<?php

namespace CustomerManagementFramework\Testing\Fixtures\View\Formatter;

class ToStringObject
{
    const TO_STRING_VALUE = 'I implement __toString()';

    /**
     * @return string
     */
    public function __toString()
    {
        return static::TO_STRING_VALUE;
    }
}
