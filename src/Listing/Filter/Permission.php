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

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Db;
use Pimcore\Model\DataObject\Listing as CoreListing;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\User;
use Pimcore\Model\User\Workspace\DataObject;

class Permission extends AbstractFilter implements OnCreateQueryFilterInterface
{
    protected $user;

    /**
     * Permission constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        // add permission conditions
        $this->addPermissionFilters($queryBuilder);
    }

    /**
     * Fetch all workspaces of user including workspaces of attached roles
     *
     * @return DataObject[]
     */
    protected function getWorkspacesOfUser()
    {
        // initialize workspaces array
        $workspaces = [];
        // fetch workspace paths from roles of user
        foreach ($this->user->getRoles() as $roleId) {
            /** @var User\Role $role */
            $role = User\Role::getById($roleId);
            foreach ($role->getWorkspacesObject() as $workspace) {
                /* @var User\Workspace\AbstractWorkspace $workspace */
                $workspaces[$workspace->getCpath()] = $workspace;
            }
        }
        //  fetch workspaces of user directly
        foreach ($this->user->getWorkspacesObject() as $workspace) {
            /* @var User\Workspace\AbstractWorkspace $workspace */
            $workspaces[$workspace->getCpath()] = $workspace;
        }
        krsort($workspaces);

        return $workspaces;
    }

    /**
     * Add user directory permissions to filter only for accessible customers
     *
     * @param QueryBuilder $queryBuilder
     */
    protected function addPermissionFilters(QueryBuilder $queryBuilder)
    {
        // fetch workspaces of user (including workspaces of roles)
        $workspaces = $this->getWorkspacesOfUser();
        // initialize allow conditions array
        $allowConditions = [];
        // initialize deny conditions array
        $denyConditions = [];
        $db = Db::get();
        $pathField = $db->quoteIdentifier(Service::getVersionDependentDatabaseColumnName('path'));
        $keyField = $db->quoteIdentifier(Service::getVersionDependentDatabaseColumnName('key'));
        foreach ($workspaces as $workspace) {
            // if user is allowed to list content -> add to allow conditions
            if ($workspace->getList()) {
                $cPath = $workspace->getCpath();
                $cPath = $cPath === '/' ? '' : $cPath;
                // prepare condition to allow sub paths (with wildcard) and path itself (with equation)
                $condition = sprintf('(CONCAT('.$pathField.','.$keyField.") LIKE '%s/%%' OR CONCAT(".$pathField.','. $keyField.") = '%s')",
                    $cPath, $cPath);
                // add allow condition
                $allowConditions[] = $condition;
            } // if user is not allowed to list content -> add to deny conditions
            else {
                // prepare condition to allow sub paths (with wildcard) and path itself (with equation)
                $condition = sprintf('(CONCAT('.$pathField.','.$keyField.") NOT LIKE '%s/%%' AND CONCAT(".$pathField.','.$keyField.") <> '%s')",
                    $workspace->getCpath(),
                    $workspace->getCpath());
                // add allow condition
                $denyConditions[] = $condition;
            }
        }
        // initialize all conditions
        $conditions = [];
        // add allow conditions
        if (!empty($allowConditions)) {
            $conditions[] = '('.implode(' OR ', $allowConditions).')';
            // add deny conditions
            if (!empty($denyConditions)) {
                $conditions[] = '('.implode(' AND ', $denyConditions).')';
            }
        }
        if (empty($conditions)) {
            $conditions[] = '0';
        }
        // add conditions
        $queryBuilder->andWhere('(' . implode(' AND ', $conditions) . ')');
    }
}
