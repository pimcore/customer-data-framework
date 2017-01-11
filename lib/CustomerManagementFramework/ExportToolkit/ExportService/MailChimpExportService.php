<?php

namespace CustomerManagementFramework\ExportToolkit\ExportService;

use Carbon\Carbon;
use DrewM\MailChimp\MailChimp;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Note;
use Pimcore\Model\Object\Concrete;

class MailChimpExportService
{
    const NOTE_TYPE = 'export.mailchimp';

    /**
     * @var MailChimp
     */
    protected $apiClient;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var Note[][]
     */
    protected $notes = [];

    /**
     * @param MailChimp $apiClient
     * @param $listId
     */
    public function __construct(MailChimp $apiClient, $listId)
    {
        $this->apiClient = $apiClient;
        $this->listId    = $listId;
    }

    /**
     * @return MailChimp
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @param null|string $subResource
     * @return string
     */
    public function getListResourceUrl($subResource = null)
    {
        $url = sprintf('lists/%s', $this->listId);

        if ($subResource) {
            $url = sprintf('%s/%s', $url, $subResource);
        }

        return $url;
    }

    /**
     * Get remote mailchimp ID as stored in export notes
     *
     * @param ElementInterface $object
     * @return string|null
     */
    public function getRemoteId(ElementInterface $object)
    {
        if ($this->wasExported($object)) {
            $note = $this->getLastExportNote($object);
            $data = $note->getData();

            if (isset($data['mailchimp_id'])) {
                return $data['mailchimp_id']['data'];
            }
        }
    }

    /**
     * @param ElementInterface $object
     * @return bool
     */
    public function wasExported(ElementInterface $object)
    {
        return null !== $this->getLastExportNote($object);
    }

    /**
     * @param ElementInterface|Concrete $object
     * @return bool
     */
    public function needsUpdate(ElementInterface $object)
    {
        // no last export -> needs update
        if (!$this->wasExported($object)) {
            return true;
        }

        if ($object->getModificationDate()) {
            $lastExportDate   = $this->getLastExportDateTime($object);
            $modificationDate = Carbon::createFromTimestamp($object->getModificationDate(), $lastExportDate->getTimezone());

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
     * @param string $remoteId
     * @param \DateTime|null $date
     * @return Note
     */
    public function createExportNote(ElementInterface $object, $remoteId, \DateTime $date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }

        $note = new Note();
        $note->setElement($object);
        $note->setDate($date->getTimestamp());
        $note->setType(static::NOTE_TYPE);
        $note->setDescription($this->listId);
        $note->setTitle('Mailchimp Export');
        $note->addData('list_id', 'text', $this->listId);
        $note->addData('mailchimp_id', 'text', $remoteId);

        return $note;
    }

    /**
     * @param ElementInterface $object
     * @param bool $refresh
     * @return Note[]|Note\Listing|\Pimcore\Model\Object\Listing\Dao
     */
    public function getExportNotes(ElementInterface $object, $refresh = false)
    {
        if (!isset($this->notes[$object->getId()]) || $refresh) {
            /** @var Note\Listing|\Pimcore\Model\Object\Listing\Dao $list */
            $list = new Note\Listing();
            $list->setOrderKey('date');
            $list->setOrder('desc');
            $list->addConditionParam('type = ?', static::NOTE_TYPE);
            $list->addConditionParam('description = ?', $this->listId);
            $list->addConditionParam('cid = ?', $object->getId());

            $this->notes[$object->getId()] = $list->load();
        }

        return $this->notes[$object->getId()];
    }

    /**
     * @param ElementInterface $object
     * @param bool $refresh
     * @return Note|null
     */
    public function getLastExportNote(ElementInterface $object, $refresh = false)
    {
        $notes = $this->getExportNotes($object, $refresh);

        if ($notes) {
            return $notes[0];
        }
    }

    /**
     * @param ElementInterface $object
     * @return \DateTime|null
     */
    public function getLastExportDateTime(ElementInterface $object)
    {
        $note = $this->getLastExportNote($object);
        if ($note) {
            return $this->getNoteDateTime($note);
        }
    }

    /**
     * @param Note $note
     * @return \DateTime
     */
    protected function getNoteDateTime(Note $note)
    {
        return Carbon::createFromTimestamp($note->getDate());
    }
}
