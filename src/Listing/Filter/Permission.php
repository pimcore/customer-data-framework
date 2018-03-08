<?php

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;
use Pimcore\Model\DataObject\Listing;
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

    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param QueryBuilder $query
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $query)
    {
        // add permission conditions
        $this->addPermissionFilters($query);
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
        foreach($this->user->getRoles() as $roleId) {
            /** @var User\Role $role */
            $role = User\Role::getById($roleId);
            foreach($role->getWorkspacesObject() as $workspace) {
                /* @var User\Workspace\AbstractWorkspace $workspace */
                $workspaces[$workspace->getCpath()] = $workspace;
            }
        }
        //  fetch workspaces of user directly
        foreach($this->user->getWorkspacesObject() as $workspace) {
            /* @var User\Workspace\AbstractWorkspace $workspace */
            $workspaces[$workspace->getCpath()] = $workspace;
        }
        krsort($workspaces);
        return $workspaces;
    }

    /**
     * Add user directory permissions to filter only for accessible customers
     *
     * @param QueryBuilder $query
     */
    protected function addPermissionFilters(QueryBuilder $query)
    {
        // fetch workspaces of user (including workspaces of roles)
        $workspaces = $this->getWorkspacesOfUser();
        // initialize allow conditions array
        $allowConditions = [];
        // initialize deny conditions array
        $denyConditions = [];
        foreach($workspaces as $workspace) {
            // if user is allowed to list content -> add to allow conditions
            if($workspace->getList()) {
                // prepare condition to allow sub paths (with wildcard) and path itself (with equation)
                $condition = sprintf("(CONCAT(o_path,o_key) LIKE '%s/%%' OR CONCAT(o_path,o_key) = '%s')",
                    $workspace->getCpath(),
                    $workspace->getCpath());
                // add allow condition
                $allowConditions[] = $condition;
            } // if user is not allowed to list content -> add to deny conditions
            else {
                // prepare condition to allow sub paths (with wildcard) and path itself (with equation)
                $condition = sprintf("(CONCAT(o_path,o_key) NOT LIKE '%s/%%' AND CONCAT(o_path,o_key) <> '%s')",
                    $workspace->getCpath(),
                    $workspace->getCpath());
                // add allow condition
                $denyConditions[] = $condition;
            }
        }
        // initialize all conditions
        $conditions = [];
        // add allow conditions
        if(!empty($allowConditions)) {
            $conditions[] = '('.implode(' OR ', $allowConditions).')';
            // add deny conditions
            if(!empty($denyConditions)) {
                $conditions[] = '('.implode(' AND ', $denyConditions).')';
            }
        }
        if(empty($conditions)) {
            $conditions[] = '0';
        }
        // add conditions
        $query->where('(' . implode(' AND ', $conditions) . ')');
    }
}