<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\SegmentBuilder;

use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractSegmentBuilder implements SegmentBuilderInterface
{
    protected PaginatorInterface $paginator;

    #[Required]
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
