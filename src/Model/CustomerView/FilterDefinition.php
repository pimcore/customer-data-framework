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

namespace CustomerManagementFrameworkBundle\Model\CustomerView;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition\Dao;
use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition\Listing;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\User;
use Pimcore\Model\User\Role;

/**
 * @method FilterDefinition\Dao getDao()
 */
class FilterDefinition extends AbstractModel
{
    /** @var int|null */
    private $id = null;

    /** @var int|null */
    private $ownerId = null;

    /** @var string */
    private $name = '';

    /** @var array */
    private $definition = [];

    /** @var array */
    private $allowedUserIds = [];

    /** @var bool */
    private $readOnly = false;

    /** @var bool */
    private $shortcutAvailable = false;

    /** @var string|null */
    private $creationDate = null;

    /** @var string|null */
    private $modificationDate = null;

    /** @var bool */
    private $isDirty = false;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerId(): int
    {
        return $this->ownerId;
    }

    /**
     * @param int $ownerId
     */
    public function setOwnerId(int $ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @param array $definition
     *
     * @return $this
     */
    public function setDefinition(array $definition): self
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedUserIds(): array
    {
        return $this->allowedUserIds;
    }

    /**
     * Set allowed user ids. Also removes duplicates and sorts ids ascending
     *
     * @param array $allowedUserIds
     *
     * @return $this
     */
    public function setAllowedUserIds(array $allowedUserIds)
    {
        // convert ids of string to ids of int
        $allowedUserIds = array_map('intval', $allowedUserIds);

        // prevent duplicate ids
        $preparedAllowedUserIds = array_unique($allowedUserIds);
        // sort ascending
        sort($preparedAllowedUserIds);
        $this->allowedUserIds = $preparedAllowedUserIds;

        return $this;
    }

    /**
     * Add single user id to allowed user ids array
     *
     * @param int $allowedUserId
     *
     * @return $this
     */
    public function addAllowedUserId(int $allowedUserId)
    {
        $this->setAllowedUserIds(array_merge($this->allowedUserIds, [$allowedUserId]));
        // return current object
        return $this;
    }

    /**
     * Add multiple user ids to allowed user ids array
     *
     * @param array $allowedUserIds
     *
     * @return $this
     */
    public function addAllowedUserIds(array $allowedUserIds)
    {
        $this->setAllowedUserIds(array_merge($this->allowedUserIds, $allowedUserIds));
        // return current object
        return $this;
    }

    /**
     * @return array
     */
    public function getShowSegments(): array
    {
        return $this->getDefinition()['showSegments'] ?? [];
    }

    /**
     * @param array $showSegments
     *
     * @return $this
     */
    public function setShowSegments(array $showSegments)
    {
        $this->definition['showSegments'] = $showSegments;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return boolval($this->readOnly);
    }

    /**
     * @param bool $readOnly
     *
     * @return $this
     */
    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShortcutAvailable(): bool
    {
        return boolval($this->shortcutAvailable);
    }

    /**
     * @param bool $shortcutAvailable
     *
     * @return $this
     */
    public function setShortcutAvailable(bool $shortcutAvailable): self
    {
        $this->shortcutAvailable = $shortcutAvailable;

        return $this;
    }

    /**
     * Load FilterDefinition object by id
     *
     * @param int $id
     *
     * @return self|null
     */
    public static function getById($id)
    {
        $cacheKey = 'cmf_customerlist_filterdefinition_'.$id;
        try {
            $filterDefinition = RuntimeCache::get($cacheKey);
            if (!$filterDefinition) {
                throw new \Exception('FilterDefinition with id '.$id.' not found in cache');
            }
        } catch (\Exception $e) {
            try {
                // create object and set id
                $filterDefinition = (new self())->setId(intval($id));
                // load filter definition by id
                $filterDefinition->getDao()->getById(intval($id));
                // save found object to cache -> Only if object was found
                RuntimeCache::set($cacheKey, $filterDefinition);
            } catch (\Exception $e) {
                // return null to indicate object not found
                return null;
            }
        }

        // return loaded FilterDefinition object
        return $filterDefinition;
    }

    /**
     * Load FilterDefinition object by id
     *
     * @param string $name
     *
     * @return self|null
     */
    public static function getByName(string $name)
    {
        try {
            // create object and set id
            $filterDefinition = new self();
            // load filter definition by id
            $filterDefinition->getDao()->getByName($name);
        } catch (\Exception $e) {
            // return null to indicate object not found
            return null;
        }

        // return loaded FilterDefinition object
        return $filterDefinition;
    }

    /**
     * @return string|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param string|null $creationDate
     *
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param string|null $modificationDate
     *
     * @return $this
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    /**
     * @param bool $isDirty
     */
    public function setIsDirty(bool $isDirty)
    {
        $this->isDirty = $isDirty;
    }

    /**
     * Saves object and reset isDirty flag
     */
    public function save()
    {
        $this->getDao()->save();
        // reset dirty state
        $this->setIsDirty(false);
    }

    /**
     * Delete object
     *
     * @return bool Returns true on deletion success otherwise false
     */
    public function delete()
    {
        return $this->getDao()->delete();
    }

    /**
     * Check if single user id is allowed to use the FilterDefinition. Must be referenced in allowedUsers or must be owner
     *
     * @param User $user
     *
     * @return bool
     */
    public function isUserAllowed(User $user)
    {
        return $this->isAnyUserAllowed($this->getUserIds($user)) || $this->getOwnerId() === $user->getId();
    }

    /**
     * Check if single user id is allowed to use the FilterDefinition. Must be referenced in allowedUsers or must be owner
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isUserIdAllowed(int $userId)
    {
        return in_array($userId, $this->getAllowedUserIds()) || $this->getOwnerId() === $userId;
    }

    /**
     * Check if any of the given user ids is allowed to use the FilterDefinition
     *
     * @param array $userIds
     *
     * @return bool
     */
    public function isAnyUserAllowed(array $userIds)
    {
        // loop through all given user ids
        foreach ($userIds as $userId) {
            // check if single user id is allowed
            if ($this->isUserIdAllowed($userId)) {
                return true;
            }
        }
        // none of the user ids is allowed
        return false;
    }

    /**
     * Check if a field should locked
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function isLocked($fieldName)
    {
        if ($this->isReadOnly() && array_key_exists($fieldName, $this->getDefinition())) {
            return true;
        }

        return false;
    }

    /**
     * Check if a segment id is locked
     *
     * @param string $segmentId
     *
     * @return bool
     */
    public function isLockedSegment($segmentId)
    {
        if ($this->isReadOnly() && array_key_exists($segmentId, @$this->getDefinition()['segments'] ?: [])) {
            return true;
        }

        return false;
    }

    /**
     * Check if segment is visible
     *
     * @param string $segmentId
     *
     * @return bool
     */
    public function isSegmentVisible($segmentId)
    {
        if (in_array($segmentId, $this->getShowSegments())) {
            return true;
        }

        return false;
    }

    /**
     * Cleans up ids from definition, allowed user ids and segments if the doesn't exist anymore
     *
     * @param bool $saveOnChange Save object if ids where removed
     *
     * @return bool Returns true if object changed otherwise false
     */
    public function cleanUp($saveOnChange = true)
    {
        // do all cleanup jobs
        $this
            ->cleanUpDefinition()
            ->cleanUpAllowedUserIds()
            ->cleanUpShowSegments();
        // check if object should be deleted
        if ($saveOnChange && $this->cleanUpOwner()) {
            // return object was deleted
            return true;
        }
        // fetch change state
        $isChanged = $this->isDirty();
        // save changes if wanted
        if ($isChanged && $saveOnChange) {
            $this->save();
        }
        // return if object changed
        return $isChanged;
    }

    /**
     * @return $this
     */
    protected function cleanUpDefinition()
    {
        // check if definition contains segments -> no segments means nothing to do
        if (!array_key_exists('segments', $this->getDefinition())) {
            return $this;
        }
        // fetch segments
        $segments = $this->getDefinition()['segments'] ?: [];
        // validate segments exist and belongs to group
        foreach ($segments as $groupId => $segmentIds) {
            // try to load segment group
            /** @noinspection MissingService */
            $segmentGroup = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroupById($groupId);
            if (!$segmentGroup) {
                // remove segment group from filter
                unset($segments[$groupId]);
                // set object needs to be saved
                $this->setIsDirty(true);
                // skip segment ids
                continue;
            }
            // try to load each segment element of group
            foreach ($segmentIds as $segmentId) {
                // fetch segment
                /** @noinspection MissingService */
                $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId);
                // check if segment found
                if (!$segment) {
                    // delete segment id from segment group
                    if (($key = array_search($segmentId, $segments[$groupId])) !== false) {
                        unset($segments[$groupId][$key]);
                        // set object needs to be saved
                        $this->setIsDirty(true);
                    }
                }
            }
        }
        // fetch full definition
        $definition = $this->getDefinition();
        // if segments left update otherwise remove from definition
        if ($segments) {
            // update segments part of definition
            $definition['segments'] = $segments;
        } else {
            // remove segments from definition
            unset($definition['segments']);
            // set object needs to be saved
            $this->setIsDirty(true);
        }
        // update segments for current object
        $this->setDefinition($definition);

        // return current object
        return $this;
    }

    /**
     * Remove invalid user ids from AllowedUserIds property
     *
     * @return $this
     */
    protected function cleanUpAllowedUserIds()
    {
        // create cleaned array
        $cleanedAllowedUserIds = [];
        // go through all user ids and check if exists
        foreach ($allowedUserIds = $this->getAllowedUserIds() as $userId) {
            // check if user or role exists
            if (User::getById($userId) || Role::getById($userId)) {
                $cleanedAllowedUserIds[] = $userId;
            }
        }
        // check for changes
        if (count($allowedUserIds) !== count($cleanedAllowedUserIds)) {
            // update allowed user ids
            $this->setAllowedUserIds($cleanedAllowedUserIds);
            // set object needs to be saved
            $this->setIsDirty(true);
        }

        // return current object instance
        return $this;
    }

    /**
     * Clean up and remove invalid segment group ids from showSegments property
     *
     * @return $this
     */
    protected function cleanUpShowSegments()
    {
        // create cleaned array
        $cleanedShowSegments = [];
        // go through show segments
        foreach ($showSegments = $this->getShowSegments() as $groupId) {
            // try to load segment group
            /** @noinspection MissingService */
            if (\Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroupById($groupId)) {
                // add to cleaned show segments
                $cleanedShowSegments[] = $groupId;
            }
        }
        // check for changes
        if (count($showSegments) !== count($cleanedShowSegments)) {
            // update show segment ids
            $this->setShowSegments($cleanedShowSegments);
            // set object needs to be saved
            $this->setIsDirty(true);
        }

        // return current object instance
        return $this;
    }

    /**
     * Check if FilterDefinition has no valid owner anymore and isn't shared with anybody and should be deleted.
     *
     * @return bool Returns true if object was deleted from database or false if object is still valid and in use
     */
    protected function cleanUpOwner()
    {
        // if user can not be loaded and doesn't exists
        if (!$this->getOwner()) {
            // and filter is not shared to anyone
            if (empty($this->getAllowedUserIds())) {
                // delete filter from database
                $this->delete();

                return true;
            }
        }

        return false;
    }

    /**
     * Fetch all FilterDefinition objects with shortcut
     *
     * @return FilterDefinition[]
     */
    public static function getAllShortcutAvailable()
    {
        $filterDefinitions = (new Listing())->load();
        $shortcutFilterDefinitions = [];
        foreach ($filterDefinitions as $filterDefinition) {
            if ($filterDefinition->isShortcutAvailable()) {
                $shortcutFilterDefinitions[] = $filterDefinition;
            }
        }

        return $shortcutFilterDefinitions;
    }

    /**
     * Fetch all FilterDefinition objects with shortcut for specific user
     *
     * @param User $user
     *
     * @return array
     */
    public static function getAllShortcutAvailableForUser(User $user)
    {
        $filterDefinitions = [];
        foreach (self::getAllShortcutAvailable() as $filterDefinition) {
            if (/*$user->isAdmin() || */
            $filterDefinition->isUserAllowed($user)) {
                $filterDefinitions[] = $filterDefinition;
            }
        }

        return $filterDefinitions;
    }

    /**
     * Prepare FilterDefinition objects for menu representation with id and name
     *
     * @param array $filterDefinitions
     *
     * @return array
     */
    public static function prepareDataForMenu(array $filterDefinitions)
    {
        $data = [];
        /** @var FilterDefinition $filterDefinition */
        foreach ($filterDefinitions as $filterDefinition) {
            $data[] = [
                Dao::ATTRIBUTE_ID => $filterDefinition->getId(),
                Dao::ATTRIBUTE_NAME => $filterDefinition->getName(),
            ];
        }

        return $data;
    }

    /**
     * Try to load owner by owner id of object.
     *
     * @return null|User\AbstractUser Returns user if found otherwise returns null
     */
    public function getOwner()
    {
        // check if owner id is set
        if (is_null($this->ownerId)) {
            return null;
        }
        // try to load user by id
        $user = User::getById($this->getOwnerId());
        // check user found
        if (!$user) {
            return null;
        }
        // return found user
        return $user;
    }

    /**
     * Check if user is owner of filter
     *
     * @param User $user
     *
     * @return bool
     */
    public function isOwner(User $user)
    {
        if ($this->getOwnerId() === $user->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is able to update existing filter definition
     *
     * @param User $user
     *
     * @return bool Returns true if user is owner of filter or filter admin
     */
    public function isUserAllowedToUpdate(User $user)
    {
        return $this->isOwner($user) || self::isFilterAdmin($user);
    }

    /**
     * Check if user is allowed to share the filter
     *
     * @param User $user
     *
     * @return bool
     */
    public function isUserAllowedToShare(User $user)
    {
        return $this->isUserAllowed($user) && self::isFilterSharer($user);
    }

    /**
     * Fetch all user ids of current user
     *
     * @param User $user
     *
     * @return array
     */
    protected function getUserIds(User $user)
    {
        // fetch roles of user
        $userIds = $user->getRoles();
        // fetch id of user
        $userIds[] = $user->getId();
        // return user ids
        return $userIds;
    }

    /**
     * Check if user is filter admin
     *
     * @param User $user
     *
     * @return bool
     */
    public static function isFilterAdmin(User $user)
    {
        return $user->isAllowed('plugin_cmf_perm_customerview_admin');
    }

    /**
     * Check if user has permission to share
     *
     * @param User $user
     *
     * @return bool
     */
    public static function isFilterSharer(User $user)
    {
        return $user->isAllowed('share_configurations');
    }
}
