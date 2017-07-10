<?php

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;

class DefaultPageSize extends Helper
{
    private $defaultPageSize = 25;

    public function getName()
    {
        return 'defaultPageSize';
    }

    /**
     * Call defaultPageSize() directly.
     *
     * @return int
     */
    public function __invoke()
    {
        return $this->defaultPageSize();
    }

    /**
     * @return int
     */
    public function defaultPageSize()
    {
        return (int) $this->defaultPageSize;
    }

    /**
     * @param int $defaultPageSize
     */
    public function setDefaultPageSize($defaultPageSize)
    {
        $this->defaultPageSize = $defaultPageSize;
    }
}
