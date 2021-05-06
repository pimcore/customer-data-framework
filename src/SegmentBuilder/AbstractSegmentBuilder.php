<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\SegmentBuilder;

use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

abstract class AbstractSegmentBuilder implements SegmentBuilderInterface
{
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @param PaginatorInterface $paginator
     * @required
     */
    public function setPaginator(PaginatorInterface $paginator): void
    {
        $this->paginator = $paginator;
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    public function executeOnCustomerSave()
    {
        return false;
    }

    public function maintenance(SegmentManagerInterface $segmentManager)
    {
    }
}
