<?php

namespace CustomerManagementFramework\Mailchimp;

use Carbon\Carbon;
use DrewM\MailChimp\MailChimp;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Note;
use Pimcore\Model\Object\Concrete;

class ExportService
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
     * @param array $entry
     * @return array|false
     */
    public function updateMember(array $entry)
    {
        return $this->apiClient->put(
            sprintf('lists/%s/members/%s', $this->listId, $this->getMemberId($entry['email_address'])),
            $entry
        );
    }

    /**
     * @param string $email
     * @return string
     */
    public function getMemberId($email)
    {
        return md5(strtolower($email));
    }

    /**
     * @param ElementInterface $object
     * @return bool
     */
    public function wasExported(ElementInterface $object)
    {
        return null !== $this->getLastExportDateTime($object);
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
     * @param ElementInterface $object
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
