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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;

use Carbon\Carbon;
use DrewM\MailChimp\MailChimp;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Db;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Note;

class MailChimpExportService
{
    const NOTE_TYPE = 'export.mailchimp';

    /**
     * @var MailChimp
     */
    protected $apiClient;

    /**
     * @param MailChimp $apiClient
     */
    public function __construct(MailChimp $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return MailChimp
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @param string $listId
     * @param null|string $subResource
     *
     * @return string
     */
    public function getListResourceUrl($listId, $subResource = null)
    {
        $url = sprintf('lists/%s', $listId);

        if ($subResource) {
            $url = sprintf('%s/%s', $url, $subResource);
        }

        return $url;
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
        if ($this->wasExported($object, $listId)) {
            $note = $this->getLastExportNote($object, $listId);
            $data = $note->getData();

            if (isset($data['mailchimp_id'])) {
                return $data['mailchimp_id']['data'];
            }
        }

        return null;
    }

    /**
     * @param string $remoteId
     *
     * @return AbstractObject|null
     */
    public function getObjectByRemoteId($remoteId)
    {
        $db = Db::get();

        return AbstractObject::getById($db->fetchOne("select cid from notes, notes_data where notes.id = notes_data.id and notes.ctype='object' and name='mailchimp_id' and notes_data.type='text' and data = ? limit 1", [$remoteId]));
    }

    /**
     * @param ElementInterface $object
     * @param string $listId
     *
     * @return bool
     */
    public function wasExported(ElementInterface $object, $listId)
    {
        return null !== $this->getLastExportNote($object, $listId);
    }

    /**
     * @param ElementInterface|Concrete $object
     * @param string $listId
     *
     * @return bool
     */
    public function needsUpdate(ElementInterface $object, $listId)
    {
        // no last export -> needs update
        if (!$this->wasExported($object, $listId)) {
            return true;
        }

        if ($object->getModificationDate()) {
            $lastExportDate = $this->getLastExportDateTime($object, $listId);
            $modificationDate = Carbon::createFromTimestamp(
                $object->getModificationDate(),
                $lastExportDate->getTimezone()
            );

            // item was modified after last export -> needs update
            if ($modificationDate > $lastExportDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getExportNoteType()
    {
        return static::NOTE_TYPE;
    }

    /**
     * @param ElementInterface $object
     * @param string $listId
     * @param string $remoteId
     * @param \DateTime|null $date
     *
     * @return Note
     */
    public function createExportNote(ElementInterface $object, $listId, $remoteId, \DateTime $date = null, $title = 'Mailchimp Export', $additionalFields = [])
    {
        if (!$date) {
            $date = Carbon::now();
        }

        $note = new Note();
        $note->setElement($object);
        $note->setDate($date->getTimestamp());
        $note->setType(static::NOTE_TYPE);
        $note->setDescription($listId);
        $note->setTitle($title);
        $note->addData('list_id', 'text', $listId);
        $note->addData('mailchimp_id', 'text', $remoteId);

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
     * @return Note[]
     */
    public function getExportNotes(ElementInterface $object, $listId, $refresh = false)
    {
        $cacheKey = 'cmf-mailchimp-export-notes';
        $notes = RuntimeCache::isRegistered($cacheKey) ? RuntimeCache::get($cacheKey) : [];

        if (!isset($notes[$listId][$object->getId()]) || $refresh) {
            $list = new Note\Listing();
            $list->setOrderKey('date');
            $list->setOrder('desc');
            $list->addConditionParam('type = ?', static::NOTE_TYPE);
            $list->addConditionParam('description = ?', $listId);
            $list->addConditionParam('cid = ?', $object->getId());

            $notes[$listId][$object->getId()] = $list->load();
        }

        RuntimeCache::set($cacheKey, $notes);

        return $notes[$listId][$object->getId()];
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
     * @param ElementInterface $object
     * @param string $listId
     * @param array $exportData
     *
     * @return bool
     */
    public function didExportDataChangeSinceLastExport(ElementInterface $object, $listId, $exportData)
    {
        if (!$note = $this->getLastExportNote($object, $listId)) {
            return true;
        }

        if ($data = $note->getData()) {
            if (isset($data['exportdataMd5']) && $data['exportdataMd5']['data'] == $this->getMd5($exportData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ElementInterface $object
     * @param string $listId
     *
     * @return \DateTime|null
     */
    public function getLastExportDateTime(ElementInterface $object, $listId)
    {
        $note = $this->getLastExportNote($object, $listId);
        if ($note) {
            return $this->getNoteDateTime($note);
        }

        return null;
    }

    public function getMd5($data)
    {
        // ensure that status_if_new and status are handled the same way in the md5 check
        $status = $data['status_if_new'] ?? $data['status'];

        unset($data['status_if_new']);
        $data['status'] = $status;

        return md5(serialize($data));
    }

    /**
     * @param Note $note
     *
     * @return \DateTime
     */
    protected function getNoteDateTime(Note $note)
    {
        return Carbon::createFromTimestamp($note->getDate());
    }
}
