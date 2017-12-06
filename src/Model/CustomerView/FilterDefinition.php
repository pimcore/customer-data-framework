<?php
/**
 * Created by PhpStorm.
 * User: dschroffner
 * Date: 04.12.2017
 * Time: 14:51
 */

namespace CustomerManagementFrameworkBundle\Model\CustomerView;

use Pimcore\Cache\Runtime;
use Pimcore\Logger;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\User;
use Pimcore\Model\User\Role;

class FilterDefinition extends AbstractModel
{
    private $id = null;
    private $name = '';
    private $definition = [];
    private $allowedUserIds = [];
    private $showSegments = [];
    private $readOnly = false;
    private $shortcutAvailable = false;
    private $creationDate = null;
    private $modificationDate = null;

    private $isDirty = false;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param null $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param $name
     * @return FilterDefinition
     */
    public function setName($name):FilterDefinition {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getDefinition(): array {
        return $this->definition;
    }

    /**
     * @param $definition
     * @return FilterDefinition
     */
    public function setDefinition(array $definition):FilterDefinition {
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
     * @param array $allowedUserIds
     * @return $this
     */
    public function setAllowedUserIds(array $allowedUserIds)
    {
        $this->allowedUserIds = $allowedUserIds;
        return $this;
    }

    /**
     * Add single user id to allowed user ids array
     *
     * @param int $allowedUserId
     * @return $this
     */
    public function addAllowedUserId(int $allowedUserId) {
        // check if allowed user id already set
        if(!in_array($allowedUserId, $this->allowedUserIds)) {
            // add user id to array
            $this->allowedUserIds[] = $allowedUserId;
        }
        // return current object
        return $this;
    }

    /**
     * Add multiple user ids to allowd user ids array
     *
     * @param array $allowedUserIds
     * @return $this
     */
    public function addAllowedUserIds(array $allowedUserIds) {
        // go through all user ids and add them
        foreach ($allowedUserIds as $userId) {
            $this->addAllowedUserId($userId);
        }
        // return current object
        return $this;
    }

    /**
     * @return array
     */
    public function getShowSegments(): array
    {
        return $this->showSegments;
    }

    /**
     * @param array $showSegments
     * @return self
     */
    public function setShowSegments(array $showSegments)
    {
        $this->showSegments = $showSegments;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool {
        return boolval($this->readOnly);
    }

    /**
     * @param $readOnly
     * @return self
     */
    public function setReadOnly(bool $readOnly):FilterDefinition {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShortcutAvailable(): bool {
        return boolval($this->shortcutAvailable);
    }

    /**
     * @param $shortcutAvailable
     * @return self
     */
    public function setShortcutAvailable(bool $shortcutAvailable):FilterDefinition {
        $this->shortcutAvailable = $shortcutAvailable;
        return $this;
    }

    /**
     * Load FilterDefinition object by id
     *
     * @param int $id
     * @return self
     */
    public static function getById($id)
    {
        $cacheKey = 'cmf_customerlist_filterdefinition_' . $id;
        try {
            $filterDefinition = Runtime::get($cacheKey);
            if (!$filterDefinition) {
                throw new \Exception('Route in registry is null');
            }
        } catch (\Exception $e) {
            try {
                // create object and set id
                $filterDefinition = (new self())->setId(intval($id));
                // load filter definition by id
                /** @noinspection PhpUndefinedMethodInspection */
                $filterDefinition->getDao()->getById(intval($id));
                // save found object to cache -> Only if object was found
                Runtime::set($cacheKey, $filterDefinition);
            } catch (\Exception $e) {
                // return null to indicate object not found
                return null;
            }
        }
        // return loaded FilterDefinition object
        return $filterDefinition;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     * @return self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * @param mixed $modificationDate
     * @return self
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
     *
     */
    public function save()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->getDao()->save();
        // reset dirty state
        $this->setIsDirty(false);
    }

    /**
     * Check if single user id is allowed to use the FilterDefinition
     *
     * @param int $userId
     * @return bool
     */
    public function isUserAllowed(int $userId) {
        return in_array($userId,$this->getAllowedUserIds());
    }

    /**
     * Check if any of the given user ids is allowed to use the FilterDefinition
     *
     * @param array $userIds
     * @return bool
     */
    public function isAnyUserAllowed(array $userIds) {
        // loop through all given user ids
        foreach ($userIds as $userId) {
            // check if single user id is allowed
            if($this->isUserAllowed($userId)) return true;
        }
        // none of the user ids is allowed
        return false;
    }

    /**
     * Check if a field should locked
     *
     * @param $fieldName
     * @return bool
     */
    public function isLocked($fieldName) {
        if($this->isReadOnly() && array_key_exists($fieldName, $this->getDefinition())) return true;
        return false;
    }

    /**
     * Check if a segment id is locked
     * @param $segmentId
     * @return bool
     */
    public function isLockedSegment($segmentId) {
        if($this->isReadOnly() && array_key_exists($segmentId, @$this->getDefinition()['segments']?:[])) return true;
        return false;
    }

    /**
     * Check if segment is visible
     *
     * @param $segmentId
     * @return bool
     */
    public function isSegmentVisible($segmentId) {
        if(in_array($segmentId, $this->getShowSegments())) return true;
        return false;
    }

    /**
     * Cleans up ids from definition, allowed user ids and segments if the doesn't exist anymore
     *
     * @param bool $saveOnChange Save object if ids where removed
     * @param bool $forceSave Enforce object to be saved regardless
     * @return bool Returns true if object changed otherwise false
     */
    public function cleanUp($saveOnChange = true, $forceSave = false) {
        // do all cleanup jobs
        $this
            ->cleanUpDefinition()
            ->cleanUpAllowedUserIds()
            ->cleanUpShowSegments();
        // fetch change state
        $isChanged = $this->isDirty();
        // save changes if wanted
        if(($isChanged && $saveOnChange) || $forceSave) $this->save();
        // return if object changed
        return $isChanged;
    }

    /**
     * @return $this
     */
    protected function cleanUpDefinition() {
        // check if definition contains segments -> no segments means nothing to do
        if(!array_key_exists('segments', $this->getDefinition())) return $this;
        // fetch segments
        $segments = $this->getDefinition()['segments']?:[];
        // validate segments exist and belongs to group
        foreach ($segments as $groupId => $segmentIds) {
            // try to load segment group
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
                $segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId);
                // check if segment found
                if(!$segment) {
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
        if($segments) {
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
    protected function cleanUpAllowedUserIds() {
        // create cleaned array
        $cleanedAllowedUserIds = [];
        // go through all user ids and check if exists
        foreach ($allowedUserIds = $this->getAllowedUserIds() as $userId) {
            // check if user or role exists
            if(User::getById($userId) || Role::getById($userId)) {
                $cleanedAllowedUserIds[] = $userId;
            }
        }
        // check for changes
        if(count($allowedUserIds) !== count($cleanedAllowedUserIds)) {
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
    protected function cleanUpShowSegments() {
        // create cleaned array
        $cleanedShowSegments = [];
        // go through show segments
        foreach ($showSegments = $this->getShowSegments() as $groupId) {
            // try to load segment group
            if (\Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentGroupById($groupId)) {
                // add to cleaned show segments
                $cleanedShowSegments[] = $groupId;
            }
        }
        // check for changes
        if(count($showSegments) !== count($cleanedShowSegments)) {
            // update show segment ids
            $this->setShowSegments($cleanedShowSegments);
            // set object needs to be saved
            $this->setIsDirty(true);
        }
        // return current object instance
        return $this;
    }
}