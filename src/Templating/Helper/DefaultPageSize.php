<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

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
        return (int)$this->defaultPageSize;
    }

    /**
     * @param int $defaultPageSize
     */
    public function setDefaultPageSize($defaultPageSize)
    {
        $this->defaultPageSize = $defaultPageSize;
    }
}
