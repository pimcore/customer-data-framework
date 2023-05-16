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

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList\MariaDbDao;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use Pimcore\Model\Listing\AbstractListing;

class DefaultMariaDbActivityList extends AbstractListing implements ActivityListInterface
{
    /**
     * @var null|int
     */
    protected $totalCount = null;

    /**
     * @var null|ActivityStoreEntryInterface[]
     */
    protected $activities = null;

    /**
     * @var MariaDbDao;
     */
    protected $dao;

    public function __construct()
    {
        $this->dao = new MariaDbDao($this);
    }

    /**
     * @return ActivityStoreEntryInterface[]
     */
    public function getActivities()
    {
        if ($this->activities === null) {
            $this->load();
            //$this->activities = [Booking::getById(5950159),Booking::getById(5950160),Booking::getById(5950161)];
        }

        return $this->activities;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function setLimit($limit): static
    {
        if ($this->limit != $limit) {
            $this->activities = null;
            $this->dao->setQuery(null);
        }
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset($offset): static
    {
        if ($this->offset != $offset) {
            $this->activities = null;
            $this->dao->setQuery(null);
        }
        $this->offset = $offset;

        return $this;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     *
     * @return ActivityStoreEntryInterface[]
     */
    public function getItems($offset, $itemCountPerPage): array
    {
        $this->setOffset($offset);
        $this->setLimit($itemCountPerPage);

        return $this->getActivities();
    }

    public function count(): int
    {
        if ($this->totalCount === null) {
            $this->totalCount = $this->dao->getCount();
        }

        return $this->totalCount;
    }

    /**
     * Return a fully configured Paginator Adapter from this method.
     */
    public function getPaginatorAdapter()
    {
        throw new \Exception('Not implemented anymore.');
    }

    public function current(): ActivityStoreEntryInterface | false
    {
        $this->getActivities();

        return current($this->activities);
    }

    public function next(): void
    {
        $this->getActivities();
        next($this->activities);
    }

    public function key(): int | null
    {
        $this->getActivities();

        return key($this->activities);
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    public function rewind(): void
    {
        $this->getActivities();
        reset($this->activities);
    }

    /**
     * @return ActivityStoreEntryInterface[]
     */
    public function load(): array
    {
        $raw = $this->dao->load();

        $this->totalCount = $this->dao->getCount();

        $activities = [];
        foreach ($raw as $row) {
            $entry = \Pimcore::getContainer()->get('cmf.activity_store')->createEntryInstance($row);
            $activities[] = $entry;
        }

        $this->activities = $activities;

        return $activities;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isValidOrderKey(string $key): bool
    {
        return true;
    }
}
