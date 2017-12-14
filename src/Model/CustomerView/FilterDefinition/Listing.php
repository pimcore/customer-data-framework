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
 * @category   Pimcore
 * @package    Staticroute
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Pimcore\Model;

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
     * @return $this
     */
    public function setFilterDefinitions($filterDefinitions)
    {
        $this->filterDefinitions = $filterDefinitions;
        return $this;
    }

    public function isValidOrderKey($key)
    {
        return $key != 'definition';
    }

    /**
     * @return FilterDefinition[]
     */
    public function load()
    {
        /** @var FilterDefinition\Listing\Dao $dao */
        $dao = $this->getDao();
        return $dao->load();
    }

    /**
     * @param array $userIds
     */
    public function setUserIdsCondition(array $userIds) {
        // check if no user ids provided
        if(empty($userIds)) return;
        // initialize conditions strings array
        $conditions = [];
        foreach ($userIds as $userId) {
            $conditions[] = 'FIND_IN_SET('.strval($userId).', ' . Dao::ATTRIBUTE_ALLOWED_USER_IDS . ') OR ' . Dao::ATTRIBUTE_OWNER_ID . '=' . strval($userId);
        }
        $this->addConditionParam('(' . implode(' OR ', $conditions) .')');
    }
}
