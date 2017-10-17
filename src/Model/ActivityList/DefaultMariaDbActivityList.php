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

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList\Dao;
use Pimcore\Model\Listing\AbstractListing;
use Zend\Paginator\Adapter\AdapterInterface;

class DefaultMariaDbActivityList extends AbstractListing implements ActivityListInterface
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var null|int
     */
    protected $totalCount = null;

    /**
     * @var null|ActivityInterface[]
     */
    protected $activities = null;

    /**
     * @var Dao;
     */
    protected $dao;

    public function __construct()
    {
        $this->dao = new Dao($this);
    }

    public function getActivities()
    {
        if ($this->activities === null) {
            $this->load();
            //$this->activities = [Booking::getById(5950159),Booking::getById(5950160),Booking::getById(5950161)];
        }

        return $this->activities;
    }

    public function setLimit($limit)
    {
        if ($this->limit != $limit) {
            $this->activities = null;
            $this->dao->setQuery(null);
        }
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setOffset($offset)
    {
        if ($this->offset != $offset) {
            $this->activities = null;
            $this->dao->setQuery(null);
        }
        $this->offset = $offset;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  int $offset Page offset
     * @param  int $itemCountPerPage Number of items per page
     *
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->setOffset($offset);
        $this->setLimit($itemCountPerPage);

        return $this->getActivities();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        if ($this->totalCount === null) {
            $this->totalCount = $this->dao->getCount();
        }

        return $this->totalCount;
    }

    /**
     * Return a fully configured Paginator Adapter from this method.
     *
     * @return AdapterInterface
     */
    public function getPaginatorAdapter()
    {
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        $this->getActivities();
        $var = current($this->activities);

        return $var;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getActivities();
        next($this->activities);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return \scalar scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        $this->getActivities();
        $var = key($this->activities);

        return $var;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $var = $this->current() !== false;

        return $var;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->getActivities();
        reset($this->activities);
    }

    public function load()
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

    public function isValidOrderKey($key)
    {
        return true;
    }
}
