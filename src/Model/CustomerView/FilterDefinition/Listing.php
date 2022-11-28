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

namespace CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Pimcore\Model;

/**
 * @method FilterDefinition\Listing\Dao getDao()
 */
class Listing extends Model\Listing\AbstractListing
{
    /**
     * Contains the results of the list. They are all an instance of Staticroute
     *
     * @var array
     */
    public $filterDefinitions = [];

    /**
     * @var null|Model\User
     */
    public $user = null;

    /**
     * @return FilterDefinition[]
     */
    public function getFilterDefinitions()
    {
        return $this->filterDefinitions;
    }

    /**
     * @param FilterDefinition[] $filterDefinitions
     *
     * @return $this
     */
    public function setFilterDefinitions($filterDefinitions)
    {
        $this->filterDefinitions = $filterDefinitions;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isValidOrderKey(/* string */ $key): bool
    {
        return $key != 'definition';
    }

    /**
     * @return FilterDefinition[]
     */
    public function load()
    {
        $dao = $this->getDao();

        return $dao->load();
    }

    /**
     * @param array $userIds
     */
    public function setUserIdsCondition(array $userIds)
    {
        // check if no user ids provided
        if (empty($userIds)) {
            return;
        }
        // initialize conditions strings array
        $conditions = [];
        foreach ($userIds as $userId) {
            $conditions[] = 'FIND_IN_SET('.strval($userId).', ' . Dao::ATTRIBUTE_ALLOWED_USER_IDS . ') OR ' . Dao::ATTRIBUTE_OWNER_ID . '=' . strval($userId);
        }
        $this->addConditionParam('(' . implode(' OR ', $conditions) .')');
    }
}
