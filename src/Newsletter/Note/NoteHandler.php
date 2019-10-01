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

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Note;


class NoteHandler
{

    protected $noteType;

    protected $remoteIdType;


    /**
     * @var Note[][][]
     */
    protected $notes = [];


    public function __construct($noteType, $remoteIdType)
    {
        $this->noteType = $noteType;

        $this->remoteIdType = $remoteIdType;
    }


    /**
     * @return string
     */
    public function getExportNoteType()
    {
        return $this->noteType;
    }

    public function getRemoteIdType() {
        return $this->remoteIdType;
    }


    /**
     * @param ElementInterface $object
     * @param string $listId
     * @param string $remoteId
     * @param \DateTime|null $date
     *
     * @return Note
     */
    public function createExportNote(ElementInterface $object, $listId, $remoteId, \DateTime $date = null, $title, $additionalFields = [])
    {
        if (!$date) {
            $date = \Carbon\Carbon::now();
        }

        $note = new Note();
        $note->setElement($object);
        $note->setDate($date->getTimestamp());
        $note->setType($this->getExportNoteType());
        $note->setDescription($listId);
        $note->setTitle($title);
        $note->addData('list_id', 'text', $listId);
        $note->addData($this->getRemoteIdType(), 'text', $remoteId);

        if (sizeof($additionalFields)) {
            foreach ($additionalFields as $key => $value) {
                $note->addData($key, 'text', $value);
            }
        }

        return $note;
    }

    /**
     * @param ElementInterface $object
     * @param string $listId
     * @param bool $refresh
     *
     * @return Note[]|Note\Listing|\Pimcore\Model\DataObject\Listing\Dao
     */
    public function getExportNotes(ElementInterface $object, $listId, $refresh = false)
    {
        if (!isset($this->notes[$listId][$object->getId()]) || $refresh) {
            /** @var Note\Listing|\Pimcore\Model\DataObject\Listing\Dao $list */
            $list = new Note\Listing();
            $list->setOrderKey('date');
            $list->setOrder('desc');
            $list->addConditionParam('type = ?', $this->getExportNoteType());
            $list->addConditionParam('description = ?', $listId);
            $list->addConditionParam('cid = ?', $object->getId());

            $this->notes[$listId][$object->getId()] = $list->load();
        }

        return $this->notes[$listId][$object->getId()];
    }

    /**
     * @param ElementInterface $object
     * @param string $listId
     * @param bool $refresh
     *
     * @return Note|null
     */
    public function getLastExportNote(ElementInterface $object, $listId, $refresh = false)
    {
        $notes = $this->getExportNotes($object, $listId, $refresh);

        if ($notes) {
            return $notes[0];
        }

        return null;
    }


    /**
     * Get remote mailchimp ID as stored in export notes
     *
     * @param ElementInterface $object
     * @param string $listId
     *
     * @return string|null
     */
    public function getRemoteId(ElementInterface $object, $listId)
    {
        if($note = $this->getLastExportNote($object, $listId) !== null) {
            $data = $note->getData();

            if (isset($data[$this->getRemoteIdType()])) {
                return $data[$this->getRemoteIdType()]['data'];
            }
        }

        return null;
    }

    public function getObjectByRemoteId($remoteId)
    {
        $db = \Pimcore\Db::get();

        return \Pimcore\Model\DataObject\AbstractObject::getById(
            $db->fetchOne("
              SELECT cid 
              FROM notes, notes_data 
              WHERE notes.id = notes_data.id 
              AND notes.ctype='object' 
              AND `name`=? 
              AND notes_data.type='text' 
              AND `data` = ? limit 1",
            $this->getRemoteIdType(), $remoteId));
    }
}
