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

namespace CustomerManagementFrameworkBundle\Pimcore\Model\Tool;

use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Paginator\Adapter\ArrayAdapter;

class ListingAdapter implements AdapterInterface
{
    /** @var \Pimcore\Model\DataObject\Listing\Concrete */
    protected $listing;

    /** @var ArrayAdapter */
    protected $adapter;

    /**
     * ListingAdapter constructor.
     *
     * @param \Pimcore\Model\DataObject\Listing\Concrete $listing
     */
    public function __construct(\Pimcore\Model\DataObject\Listing\Concrete $listing)
    {
        $this->listing = $listing;
    }

    /**
     * @return ArrayAdapter
     */
    protected function adapter()
    {
        if ($this->adapter === null) {
            if ($this->listing->count() > 0) {
                $this->adapter = new ArrayAdapter($this->listing->loadIdList());
            } else {
                // create empty
                $this->adapter = new ArrayAdapter([]);
            }
        }

        return $this->adapter;
    }

    public function count()
    {
        return $this->adapter()->count();
    }

    /**
     * @param int $offset
     * @param int $itemCountPerPage
     *
     * @return \Pimcore\Model\DataObject\Concrete[]
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return array_map(
            function ($id) {
                return \Pimcore\Model\DataObject\Concrete::getById($id);
            },
            $this->adapter()->getItems($offset, $itemCountPerPage)
        );
    }
}
